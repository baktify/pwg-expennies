import axios from "axios";

const init = () => {
    test()
}

const test = async () => {
    const response = await axios.put(`http://localhost:8000/reset-password/b735d1bb2c44a39a948ad94c7c94c97ca279ed342ea4f41be7684b32dd9c9549?expiration=1704921000&signature=65e623c5b1450774292372d8af4abeafbc74b5ea69d7fbda390dc3dd093c3238`, {
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