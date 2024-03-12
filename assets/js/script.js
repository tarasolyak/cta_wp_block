function sendForm() {
    const form = document.querySelector("form")
    const name_field = document.querySelector(".ctablock__item_name")
    const email_field = document.querySelector(".ctablock__item_email")
    const name__error = document.querySelector(".name__error")
    const phone_field = document.querySelector(".ctablock__item_phone")
    const code_country = document.querySelector(".ctablock__item_code")
    const phone__error = document.querySelector(".phone__error")
    const email__error = document.querySelector(".email__error")
    const btn = document.querySelector(".btn > button") 
    const spinner = document.querySelector(".spinner")
    const button_name = document.querySelector(".btn_name")
    const active_code_country = document.querySelector(".flag__active")
    code_country.value = active_code_country.dataset.code
    
    


    function validateName(name) {
        if (name === "") {
            return {status: false, error_text: "Поле обовязкове для заповнення"}
        }

        if (name.slice(0,1) == " ") {
            return {status: false, error_text: "Перший символ не може бути пробілом"}
        }

        let result_match = name.match(/^([a-zA-Z]|[А-Яа-я])+(\s([a-zA-Z]|[А-Яа-я])+)*$/)

        if (result_match === null || result_match === undefined) {
            return {status: false, error_text: "Введене імя некоректне (введіть ім'я без чисел та пробілів)"}
        }

        return {status: true, error_text: ""}
        
    }

    function validatePhone(phone) {

        const code_wth = active_code_country.dataset.code

        if (phone === "") {
            return {status: false, error_text: "Поле обовязкове для заповнення"}
        }

        phone = phone.replaceAll(" ", '')

        let match = phone.match(/^\d+$/)

        if (match === null || match === undefined) {
            return {status: false, error_text: "Введіть тільки числа"}
        }

        const code_countries = {
            380:12,
            48:7,
            32:5,
            420:11,
            33: 13
        }

       for (const key in code_countries) {
            if (key == code_wth) {
                let number_phone = code_wth + phone;

                if (code_countries[key] !== number_phone.length) {
                    return {status: false, error_text: "Неправильний набір номеру"}
                }
            }
       }
        
        return {status: true, error_text: ""}
    }

    function validateEmail(email) {
        if (email.trim() === "") {
            return { status: true, error_text: "" }; 
        }
    
        let result = email.match(/^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/);
        if (!result) {
            return { status: false, error_text: "Некоректно введений адрес" };
        }
        return { status: true, error_text: "" };
    }

    function show_error(elemField, elemError) {
        document.querySelector(elemField).classList.add("error_border");
        document.querySelector(elemError).classList.add("error_validate");
    }

    function style_remove(elemField, elemError) {
        
        elemField.classList.remove("error_border");
        elemError.classList.remove("error_validate")
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const param_name = validateName(e.target.name.value)
        const param_phone = validatePhone(e.target.phone.value)
        const param_mail = validateEmail(e.target.mail.value)

        if (param_name.status === false) {
            show_error(".ctablock__item_name", ".name__error");
            name__error.textContent = param_name.error_text;
        } else {
            style_remove(name_field, name__error);
            name__error.textContent = param_name.error_text;
        }

        if (param_phone.status === false) {
            show_error(".ctablock__item_phone", ".phone__error");
            phone__error.textContent = param_phone.error_text;
        } else {
            style_remove(phone_field, phone__error);
            phone__error.textContent = param_phone.error_text;
        }

        if (param_mail.status === false) {
            show_error(".ctablock__item_email", ".email__error");
            email__error.textContent = param_mail.error_text;
        } else {
            style_remove(email_field, email__error);
            email__error.textContent = param_mail.error_text;
        }

        // btn form
        if (param_name.status === false || param_phone.status === false || param_mail.status === false) {
            return
        } else {

            btn.setAttribute("disabled", "")

            const formData = new FormData(form);

            console.log("formData ", formData)

            const body = {method: "POST", body: formData}

            button_name.style.display = "None";
            spinner.classList.add('spinner_active')

            const res = await fetch(ajaxurl.ajax_url, body)

            const data = await res.json();
			
			console.log(data)

            if (!res.ok || data.status === "error") {
                if (data.error_name_field == true) {
                    show_error(".ctablock__item_name", ".name__error") 
                    name__error.textContent = "Помилка валідації. Спробуйте ще раз";
                }
                
                if (data.error_email_field == true) {
                    show_error(".ctablock__item_name", ".name__error") 
                    name__error.textContent = "Помилка валідації. Спробуйте ще раз";
                } 
                
				button_name.style.display = "block";
                spinner.classList.remove('spinner_active')
                btn.removeAttribute("disabled")
                button_name.textContent = "Помилка! Спробуйте пізніше."
				setTimeout(() => {
                    button_name.textContent = "Надіслати"
                }, 6000)
                throw new Error(`HTTP error! status: ${res.status}`);

            } else {
                if (data.status === "success") {
                    console.log("Форма відправлена")
                    
                    show_popup()
                    e.target.name.value = ""
                    e.target.mail.value = ""
                    e.target.phone.value = ""
                    e.target.message.value = ""
                    button_name.style.display = "block";
                    spinner.classList.remove('spinner_active')
                    btn.removeAttribute("disabled")
                }
            }
        }   
    })
}

sendForm()

const countries = document.querySelector(".ctablock__countries")
const all_countries = document.querySelectorAll(".ctablock__countries li")
const popup = document.querySelector(".popup")
const close_popup = document.querySelector(".popup__close")
const cta_block = document.querySelector(".ctablock")

function show_popup() {
    cta_block.style.display = "none"
    popup.style.display = "flex"
}

function hide_popup() {
    close_popup.addEventListener("click", () => {
        cta_block.style.display = "flex"
        popup.style.display = "none"
    })
}

hide_popup()

function arr_countries() {
    all_countries.forEach(elem => {
        elem.classList.remove("flag__active")
    })
}

all_countries.forEach(elem => {
    elem.addEventListener("click", () => {
        arr_countries()
        elem.classList.add("flag__active")
    })
})

countries.addEventListener("click", (e) => {
  
    countries.classList.toggle("ctablock__countries_active");
    document.querySelector(".ctablock__item_code").value = document.querySelector(".flag__active").dataset.code

})  
