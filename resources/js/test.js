import axios from "axios";

const init = () => {
    test()
}

const test = async () => {
    const response = await axios.post(`http://localhost:8000/reset-password/b9c559f9a345f79aeab44376cbf6994d638e41abe0b0ac357a1e5ffcf37ef7e3`, {
        ...getCsrfFields(),
        password: 'qqq',
        confirmPassword: 'qqq',
    }, {
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })

    console.log(response)
}

const getCsrfFields = () => {
    const csrfNameKey = document.querySelector('#csrfName').getAttribute('name')
    const csrfName = document.querySelector('#csrfName').getAttribute('content')
    const csrfValueKey = document.querySelector('#csrfValue').getAttribute('name')
    const csrfValue = document.querySelector('#csrfValue').getAttribute('content')

    return {
        [csrfNameKey]: csrfName,
        [csrfValueKey]: csrfValue
    }
}

document.addEventListener('DOMContentLoaded', init)