import {saveProfile} from './requests.js';

const init = () => {
    const saveProfileBtn = document.querySelector('.save-profile')

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

    saveProfileBtn.addEventListener('click', onClickSaveProfileBtn)
}

document.addEventListener('DOMContentLoaded', init)