import {saveProfile, updatePassword, clearErrors} from './requests.js';
import {Modal} from "bootstrap"

const init = () => {
    const saveProfileBtn = document.querySelector('.save-profile')
    const updatePasswordBtn = document.querySelector('.update-password')
    const updatePasswordForm = updatePasswordBtn.closest('form')
    const updatePasswordModal = new Modal('#updatePasswordModal')

    const onClickSaveProfileBtn = () => {
        const saveProfileForm = saveProfileBtn.closest('form')
        const formData = Object.fromEntries(new FormData(saveProfileForm));

        console.log(saveProfileForm)

        saveProfileBtn.classList.add('disabled')

        saveProfile(formData).then(({status, data}) => {
            saveProfileBtn.classList.remove('disabled')

            if (status === 200) {
                alert('Profile has been updated.');
            }
        })
    }

    const onSubmitUpdatePasswordForm = (e) => {
        e.preventDefault()

        const formData = Object.fromEntries(new FormData(e.target))

        updatePassword(updatePasswordForm.action, formData, updatePasswordForm).then(response => {
            if (response.status === 200) {
                alert('You password has been updated.')
                updatePasswordModal.hide()
                updatePasswordForm.reset()
            }
        })
    }

    const onHideUpdatePasswordModal = () => {
        updatePasswordForm.reset()
        clearErrors(updatePasswordForm)
    }

    updatePasswordModal._addEventListeners('hidden.bs.modal', onHideUpdatePasswordModal)
    saveProfileBtn.addEventListener('click', onClickSaveProfileBtn)
    updatePasswordForm.addEventListener('submit', onSubmitUpdatePasswordForm)
}

document.addEventListener('DOMContentLoaded', init)