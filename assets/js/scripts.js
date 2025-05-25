   const words = ["Peliculas", "Series", "Documentales" ];
        let index = 0;
        const dynamicWord = document.getElementById("dynamic-word");

        function changeWord() {
            index = (index + 1) % words.length;
            dynamicWord.textContent = words[index];
        }

        dynamicWord.addEventListener('animationiteration', changeWord);
		
		
		
 
 
 
const navbar = document.querySelector(".navbar");
const navbarLinks = document.querySelector(".links");
const hamburger = document.querySelector(".hamburger");

hamburger.addEventListener("click", () => {
    navbar.classList.toggle("active");
    navbarLinks.classList.toggle("active");
})

/*FAQs*/
const accordionItemHeaders = document.querySelectorAll(".accordion-item-header");

accordionItemHeaders.forEach(accordionItemHeader => {
    accordionItemHeader.addEventListener("click", () => {
        accordionItemHeader.classList.toggle("active");
        const accordionItemBody = accordionItemHeader.nextElementSibling;

        if(accordionItemHeader.classList.contains("active")) accordionItemBody.style.maxHeight = accordionItemBody.scrollHeight + "px";
        else accordionItemBody.style.maxHeight = "0px";
    });
});

 
const serverName = document.querySelector(".server-name");
const serverLogo = document.querySelector(".logo-img");
 
const serverIp = document.querySelector(".minecraft-server-ip");
const serverLogoHeader = document.querySelector(".logo-img-header");
const discordOnlineUsers = document.querySelector(".discord-online-users");
const minecraftOnlinePlayers = document.querySelector(".minecraft-online-players");
 
const contactForm = document.querySelector(".contact-form");
const inputWithLocationAfterSubmit = document.querySelector(".location-after-submit");

const getDiscordOnlineUsers = async () => {
    
}
		
