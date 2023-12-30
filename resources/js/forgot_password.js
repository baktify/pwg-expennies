import {requestPasswordReset, updatePassword} from "./requests";

const init = () => {
    const forgotPasswordBtn = document.querySelector('.forgot-password-btn')
    const resetPasswordBtn = document.querySelector('.reset-password-btn')

    if (forgotPasswordBtn) {
        const form = forgotPasswordBtn.closest('form')

        const onSubmitForgotPasswordForm = (e) => {
            e.preventDefault()

            const formData = Object.fromEntries(new FormData(form))

            requestPasswordReset(formData, form).then(({status}) => {
                if (status) {
                    alert('An email with instructions to reset your password has been sent.')
                }

                window.location = '/login'
            })
        }

        form.addEventListener('submit', onSubmitForgotPasswordForm)
    }

    if (resetPasswordBtn) {
        const onClickResetPasswordBtn = () => {
            const form = resetPasswordBtn.closest('form')
            const formData = Object.fromEntries(new FormData(form));

            updatePassword(form.action, formData, form).then(({status}) => {
                if (status) {
                    alert('You password has been updated. You will be redirected to login page')

                    window.location = '/'
                }
            })
        }

        resetPasswordBtn.addEventListener('click', onClickResetPasswordBtn)
    }
}

document.addEventListener('DOMContentLoaded', init)