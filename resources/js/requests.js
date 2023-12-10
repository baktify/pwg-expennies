import axios from "axios";

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

export const updateCategory = async (id, newName) => {
    const response = await axe.put(`/categories/${id}`, {
        name: newName,
        ...getCsrfFields()
    })
    return response.data
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