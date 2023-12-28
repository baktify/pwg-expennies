import "../css/auth.scss"
import {logIn} from "./requests.js"
import {Modal} from "bootstrap"

const init = () => {
    const loginBtn = document.querySelector('.log-in-btn')
    const loginForm = loginBtn.closest('form')
    const twoFactorAuthModal = new Modal('#twoFactorAuthModal')

    const onSubmitLoginForm = (event) => {
        event.preventDefault()

        const formData = Object.fromEntries((new FormData(event.target)))

        logIn(formData, event.target).then(({status, data}) => {
            if (data.two_factor) {
                twoFactorAuthModal.show()
            } else {
                window.location = '/'
            }
        })
    }

    loginForm.addEventListener('submit', onSubmitLoginForm)
}

document.addEventListener('DOMContentLoaded', init)