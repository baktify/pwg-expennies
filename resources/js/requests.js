import axios from "axios";
import config from "bootstrap/js/src/util/config";

const axe = axios.create({
    baseURL: 'http://localhost:8000',
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    }
})

export const getCategory = async (id) => {
    const {data} = await axe.get(`/categories/${id}`)
    return data
}

export const updateCategory = async (id, newName, domElement) => {
    try {
        clearErrors(domElement)

        const {status, data} = await axe.put(`/categories/${id}`, {
            name: newName,
            ...getCsrfFields()
        })

        return {status, data}
    } catch ({response: {status, data}}) {
        return handleErrors({
            status,
            errors: data
        }, domElement)
    }
}

export const deleteCategory = async (id) => {
    await axe.post(`/categories/${id}`, {
        ...getCsrfFields(),
    }, {
        headers: {
            'X-Http-Method-Override': 'DELETE'
        }
    })
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

const handleErrors = ({errors}, domElement) => {
    for (const error in errors) {
        const input = domElement.querySelector(`input[name="${error}"]`)
        input.classList.add('is-invalid')

        for (const message of errors[error]) {
            const errorDiv = `<div class="invalid-feedback">${message}</div>`
            input.parentElement.innerHTML += errorDiv
        }
    }
}

const clearErrors = (domElement) => {
    const errorInputs = domElement.querySelectorAll('.is-invalid')
    const errorDivs = domElement.querySelectorAll('.invalid-feedback')

    for (const errorInput of errorInputs) {
        errorInput.classList.remove('is-invalid')
    }

    for (const errorDiv of errorDivs) {
        errorDiv.remove()
    }
}