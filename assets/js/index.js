$(document).ready(function() {
    var images = [
        "fudbal-dresovi/29.png",
        "golmanski/2.png",
        "košarka/ženski/2.png",
        "košarka-trenerke/2.png"
    ];
    var currentIndex = 0;

    setInterval(function() {
        currentIndex = (currentIndex + 1) % images.length;
        $("#landing-png").attr("src", ("assets/img/png/" + images[currentIndex]));
    }, 3000);

    // Select the first element with class "icon-box" under the #services element
    const firstIconBox = document.querySelector("#services .icon-box");

    if (firstIconBox) {
    // Get the height of the first "icon-box" element
    const firstIconBoxHeight = firstIconBox.offsetHeight;

    // Select all "icon-box" elements under the #services element
    const allIconBoxes = document.querySelectorAll("#services .icon-box");

    // Apply the height of the first element to all others
    allIconBoxes.forEach(iconBox => {
        iconBox.style.height = `${firstIconBoxHeight}px`;
        iconBox.classList.add("row");
        iconBox.classList.add("align-items-center");
        iconBox.style.textAlign = "justify";
    });
    }
});