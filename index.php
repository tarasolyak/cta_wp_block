<?php get_header() ?>

<div class="container">
    <div class="ctablock">
        <div class="ctablock__wrapper">
            <div class="ctablock__content">
                <img src="<?php echo get_stylesheet_directory_uri() ?>/assets/img/s.svg" alt="">
                <p>Ми завжди готові запропонувати інноваційні та альтернативні шляхи лікування зубів</p>
            </div>
            <div class="ctablock__form">
                <form id="main_form" action="" method="POST">
                    <p>Заповніть форму та отримайте професійну консультацію</p>
                    <div class="ctablock__item name">
                        <label for="name">Ваше ім’я</label>
                        <input class="ctablock__item_name" type="text" name="name" id="name" placeholder="Вкажіть Ваше ім’я">
                        <span class="name__error">Вкажіть Ваше ім’я</span>
                    </div>
                    <div class="ctablock__item">

                        <label for="phone">Ваш телефон</label>
                        <div class="ctablock__wrap">
                            <ul class="ctablock__countries">
                                <li class="flag flag__ua flag__active" data-code="380">
                                    <span>+380</span>
                                <li class="flag flag__pl" data-code="48">
                                    <span>+48</span>
                                </li>
                                <li class="flag flag__bl" data-code="32">
                                    <span>+32</span>
                                </li>
                                <li class="flag flag__cz" data-code="420">
                                    <span>+420</span>
                                </li>
                                <li class="flag flag__fr" data-code="33">
                                    <span>+33</span>
                                </li>
                            </ul>
                            <input type="tel" class="ctablock__item_phone" name="phone" id="phone">
                            <input type="hidden" class="ctablock__item_code" name="code">
                        </div>
                        
                        <span class="phone__error">Error</span>
                    </div>
                    <div class="ctablock__item">
                        <label for="mail">Ваш e-mail</label>
                        <input class="ctablock__item_email" type="email" name="mail" id="mail" placeholder="email@gmail.com">
                        <span class="email__error">Error</span>
                    </div>
                    <div class="ctablock__item">
                        <textarea name="message" id="message" cols="30" rows="10" placeholder="Коротко опишіть проблему,
яку хочете вирішити"></textarea>
                    </div>
                    <div class="ctablock__item btn">
                    
                        <button type="submit">
                            <svg class="spinner" viewBox="0 0 50 50">
                                <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                            </svg>
                            <span class="btn_name">Надіслати</span>
                        </button>
                    </div>
                    <input type="hidden" name="action" value="leadpost" />
                </form>
                <p>
                Натискаючи на кнопку, я даю згоду <a href="">
                на обробку персональних даних
                </a></p>
            </div>
        </div>
    </div>
    <div class="popup">
        <div class="popup__wrapper">
            <div class="popup__close">Закрити</div>
            <img src="<?php echo get_stylesheet_directory_uri() ?>/assets/img/raketa.png" alt="">
            <span>Ваш запит надіслано</span>
            <p>Дякуємо,<br> що довіряєте!</p>
        </div>
    </div>
</div>
    

<?php get_footer() ?>