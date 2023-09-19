let count = 0;
function addSlide() {
    // Create a new slide
    let newSlide = document.createElement('div');
    newSlide.className = 'slide';
    newSlide.innerText = 'New Content' + count;
    count++;

    // Append the new slide to the container
    let container = document.querySelector('.carouselList-container');
    container.appendChild(newSlide);

    // Move the slides upwards
    const timeoutA = setTimeout(()=>{
        let slides = document.querySelectorAll('.slide');
        for (let slide of slides) {
            slide.style.transition = 'transform 0.4s ease-in-out';
            slide.style.transform = `translateY(-50px)`;
        }
    }, 100)


    // Remove the first slide after the transition
    const timeoutB = setTimeout(() => {
        let slides = document.querySelectorAll('.slide');
        container.removeChild(slides[0]);
        // Reset transform for remaining slides
        for (let slide of document.querySelectorAll('.slide')) {
            slide.style.transition = 'none'
            slide.style.transform = `translateY(0px)`;
        }
    }, 500);
}
