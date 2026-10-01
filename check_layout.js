const puppeteer = require('puppeteer');
(async () => {
    try {
        const browser = await puppeteer.launch();
        const page = await browser.newPage();
        await page.setViewport({width: 360, height: 740});
        await page.goto('http://localhost/farmersbd/index.php', { waitUntil: 'networkidle0' });
        
        const data = await page.evaluate(() => {
            const row = document.querySelector('.hero-stats-image-row');
            const heroContent = document.querySelector('.hero-content');
            const carouselInner = document.querySelector('.carousel-inner');
            const carouselItem = document.querySelector('.carousel-item.active');
            const img = document.querySelector('.carousel-item.active img');
            
            return {
                heroContent_pos: window.getComputedStyle(heroContent).position,
                heroContent_height: window.getComputedStyle(heroContent).height,
                carouselItem_minHeight: window.getComputedStyle(carouselItem).minHeight,
                carouselItem_height: window.getComputedStyle(carouselItem).height,
                img_pos: window.getComputedStyle(img).position
            };
        });
        
        console.log(JSON.stringify(data, null, 2));
        await browser.close();
    } catch (e) {
        console.log(e);
    }
})();
