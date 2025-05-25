TOKEN = '8047309440:AAEPy3xsv6iR5kldSoMjiNxzIFps4a-cA7w'
URL_BASE = "http://localhost/"
from pdf2image import convert_from_path
from weasyprint import HTML
import re
import random
import json
import logging
from telegram import Update, InlineKeyboardButton, InlineKeyboardMarkup
from telegram.ext import ApplicationBuilder, CommandHandler, CallbackQueryHandler, ContextTypes, MessageHandler, filters
import aiofiles
import asyncio, aiohttp
import imgkit
from telegram import Bot
import os
from datetime import datetime
telegram_id = 7623415125 #CAMBIAR
idPrincipal = 1588098273 #CAMBIAR
user_states = {}

ACTIVO = True

options = {
    'quality': '100'  # Puedes ajustar el valor entre 0 y 100
}
base_url = 'https://fonts.googleapis.com/'
async def log_message(message):
    timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    formatted_message = f"[{timestamp}] {message}"
    async with asyncio.Lock():  # Para evitar problemas de concurrencia si se llama varias veces.
        async with aiofiles.open("log.txt", "a") as log_file:
            await log_file.write(formatted_message + "\n")

# Configurar el registro de logs
logging.basicConfig(format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
                    level=logging.INFO)
logger = logging.getLogger(__name__)

async def enviar_mensaje(telegram_id, mensaje):
        bot = Bot(token=TOKEN)
        await bot.send_message(chat_id=telegram_id, text=mensaje)

async def load_data_from_json(filepath):
    async with aiofiles.open(filepath, 'r', encoding='utf-8') as file:
        content = await file.read()
        data = json.loads(content)  # Convertir a diccionario
    return data

async def send_telegram_photo(image_path,chat_id):
    bot = Bot(token=TOKEN)
    if not os.path.exists(image_path):
        print(f"El archivo {image_path} no existe.")
        return

    async with aiohttp.ClientSession() as session:
        with open(image_path, 'rb') as f:
            photo_bytes = f.read()  # Lee la imagen en bytes
            await bot.send_photo(chat_id=chat_id, photo=photo_bytes)
    try:
        os.remove(image_path)
        print(f"Foto {image_path} borrada.")
    except OSError as e:
        print(f"Error borrando la foto: {e}")

async def getCuentas(users,chatId):
    cuentas = []
    chatId_str = str(chatId)
    for key in users:
        if chatId_str in key.split(','):
            cuentas.extend(users[key])
    return cuentas
# Función para manejar el comando /start
async def start(update: Update, context: ContextTypes.DEFAULT_TYPE):
    print(update.message)
    chatId = update.message.chat.id
    user_states[chatId] = "awaiting_buttons"
    name = update.message.chat.username

    users = await load_data_from_json('users.json')
    cuentas = await getCuentas(users,chatId)
    if(cuentas == {}):
        await update.message.reply_text(
            text='No tienes permiso para usar este bot.')
        await enviar_mensaje(idPrincipal,"El usuario con id: "+str(chatId)+" y nombre de usuario: "+name+" no ha podido acceder al panel")
    else:
        keyboard = [
            [InlineKeyboardButton("🤖 Netflix", callback_data='netflix')], #cambiar
            [InlineKeyboardButton("🤖 Prime Video", callback_data='prime')],
            [InlineKeyboardButton("🤖 Disney+", callback_data='disney')]
#            [InlineKeyboardButton("🤖 Max", callback_data='max')]
        ]
        reply_markup = InlineKeyboardMarkup(keyboard)
        await update.message.reply_text('¡Hola! Para solicitar un codigo, elige el servicio que deseas consultar.', reply_markup=reply_markup)

async def generate_keyboard(data, user_id, main_key):
    user_states[user_id] = main_key
    data = await load_data_from_json('json/platform_keywords.json')
    subject_keywords = data.get(main_key, {}).get("SUBJECT_KEYWORDS", {})
    keyboard = []
    for key in subject_keywords:
        print(key)
        keyboard.append([InlineKeyboardButton(f"{key}", callback_data=f'subopcion_{main_key} - {key}')])
    keyboard.append([InlineKeyboardButton("Volver al menú principal", callback_data='volver')])
    return InlineKeyboardMarkup(keyboard)

async def volver_menu_principal(query):
    keyboard = [
        [InlineKeyboardButton("🤖 Netflix", callback_data='netflix')],  #cambiar
        [InlineKeyboardButton("🤖 Prime Video", callback_data='prime')],
        [InlineKeyboardButton("🤖 Disney+", callback_data='disney')]
#        [InlineKeyboardButton("🤖 MAX", callback_data='max')]
    ]
    reply_markup = InlineKeyboardMarkup(keyboard)
    await query.edit_message_text(text='Elige una plataforma para ver el código:', reply_markup=reply_markup)

async def menu_reclamacion(query):
    keyboard = [
        [InlineKeyboardButton("Cuenta caida", callback_data='cuenta_caida')],
        [InlineKeyboardButton("No logro recibir código", callback_data='recibir_codigo')]
    ]
    reply_markup = InlineKeyboardMarkup(keyboard)
    await query.edit_message_text(text='Elige el tipo de reclamación:', reply_markup=reply_markup)

# Función para manejar las pulsaciones de los botones
async def button_handler(update: Update, context: ContextTypes.DEFAULT_TYPE):
    query = update.callback_query
    await query.answer()
    chatId = query.message.chat.id
    users = await load_data_from_json('users.json')
    cuentas = await getCuentas(users,chatId)
    data = await load_data_from_json('json/platform_keywords.json')
    if(cuentas == {}):
        await query.edit_message_text(
            text='No tienes permiso para usar este bot.')
    else:
        if query.data.startswith('subopcion'):
            user_states[chatId] = f"{query.data.replace('subopcion_', ' ')}"
            await query.edit_message_text(text=f"Seleccionaste: {query.data.replace('subopcion_', ' ').title()}. Por favor envía el correo electrónico que deseas buscar.")
        elif query.data == 'volver':
            await volver_menu_principal(query)
        elif query.data == 'reclamar_cuentas':
            await menu_reclamacion(query)
        else:
            await query.edit_message_text(
                text='¡Vamos a ello! ¿Qué opción necesitas?',
                reply_markup=await generate_keyboard(data, chatId, query.data.lower())
            )

async def fetch(session, url, params):
    async with session.get(url, params=params) as response:
        # Verifica el tipo de contenido
        content_type = response.headers.get('Content-Type', '')
        text_response = await response.text()
        return text_response

async def getStatusCorreo(p,correo,id):
    url = URL_BASE+"correo_valido.php"
    requests_params = {
                "p": p,
                "email": correo,
                "password": id
            }

    async with aiohttp.ClientSession() as session:
        task = await fetch(session, url, requests_params)

        response = json.loads(task)

        if "response" in response:
            return "OK"
        elif "error" in response:
            return response["error"]

async def comprobarCorreo(p,correo,id,type):
    url = URL_BASE+"consultar_correo.php"
    requests_params = [
        {
            "p": p,
            "email": correo,
            "password": id,
            "type": type
        }
    ]

    async with aiohttp.ClientSession() as session:
        tasks = []
        for params in requests_params:
            task = fetch(session, url, params)
            tasks.append(task)
        print(task)
        responses = await asyncio.gather(*tasks)

        return responses


async def text_handler(update: Update, context: ContextTypes.DEFAULT_TYPE):
    global ACTIVO
    user_message = update.message.text
    user_id = update.message.chat.id
    print(user_id)
    text = update.message.text
    if(text=="#"):
        ACTIVO = not ACTIVO
    if(user_states[user_id] and ACTIVO):
        if(user_states[user_id]=="reclamacion"):
            await enviar_mensaje(telegram_id, user_message)
            user_states[user_id]="awaiting_buttons"
        if(user_states[user_id]!="awaiting_buttons"):
            status = await getStatusCorreo("netflix",text,user_id)
            if(status=="OK"):
                await update.message.reply_text("Buscando correos... 🔎")
                info = user_states[user_id].split(" - ")
                informacion = str(await comprobarCorreo(info[0].lower().replace(" ",""),text,user_id,info[1]))
                if("IMAGEN1234" in informacion):
                    informacion = informacion.replace("IMAGEN1234", "").replace("\\r\\n","").replace("['","").replace("\\xa0","")
                    informacion_limpia = re.sub(r'about:blank', '', informacion)
                    pdf_path = "/var/www/html/images/output"+str(random.randint(0,10000))+".pdf"
                    html = HTML(string=informacion, base_url=base_url)
                    html.write_pdf(pdf_path)
                    images = convert_from_path(pdf_path)
                    os.remove(pdf_path)
                    image_path = pdf_path.replace("pdf","png")
                    images[0].save(image_path, 'PNG')
                    await send_telegram_photo(image_path,user_id)
                else:
                    if("No se encontraron correos recientes" in informacion):
                        await update.message.reply_text(informacion)
                    else:
                        await update.message.reply_text("Correo consultado: "+str(text)+"\nLink extraido: \n"+str(informacion).replace("[\'","").replace("\']","").replace("amp;",""))
            else:
                await update.message.reply_text(status)


application = ApplicationBuilder().token(TOKEN).build()

# Agregar manejadores de comandos y callback
application.add_handler(CommandHandler('start', start))
application.add_handler(CallbackQueryHandler(button_handler))
application.add_handler(MessageHandler(filters.TEXT & ~filters.COMMAND, text_handler))

# Iniciar el bot con polling infinito
if __name__ == '__main__':
    application.run_polling()
