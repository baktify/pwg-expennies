import "../css/auth.scss"
import {logIn, twoFactorLogIn} from "./requests.js"
import {Modal} from "bootstrap"

const init = () => {
    const loginBtn = document.querySelector('.log-in-btn')
    const loginForm = loginBtn.closest('form')
    const twoFactorAuthModal = new Modal('#twoFactorAuthModal')
    const twoFactorLoginBtn = document.querySelector('.log-in-two-factor')
    const twoFactorLoginForm = twoFactorLoginBtn.closest('form')

    console.log(twoFactorLoginBtn, twoFactorLoginForm)

    const onSubmitTwoFactorLoginForm = (e) => {
        e.preventDefault()

        const formData = Object.fromEntries(
            new FormData(e.target)
        )

        twoFactorLogIn(formData, e.target).then(({status}) => {
            if (status === 200) {
                window.location = '/'
            }
        })
    }

    const onSubmitLoginForm = (e) => {
        e.preventDefault()

        const formData = Object.fromEntries((new FormData(e.target)))

        logIn(formData, e.target).then(({status, data}) => {
            if (data.two_factor) {
                twoFactorAuthModal.show()
            } else {
                window.location = '/'
            }
        })
    }

    loginForm.addEventListener('submit', onSubmitLoginForm)
    twoFactorLoginForm.addEventListener('submit', onSubmitTwoFactorLoginForm)
}

document.addEventListener('DOMContentLoaded', init)