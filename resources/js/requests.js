import axios from "axios";

const axe = axios.create({
    baseURL: 'http://localhost:8000',
    headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    }
})

export const logIn = async (formData, parentDom) => {
    try {
        clearErrors(parentDom)

        const {status, data} = await axe.post('/login', {
            ...formData,
            ...getCsrfFields()
        })

        return {status, data}
    } catch ({status, response: {data: errors}}) {
        handleErrors(errors, parentDom)
    }
}

/** Transaction requests */
export const toggleTransactionReview = async (transactionId) => {
    try {
        const {status, data} = await axe.put(`/transactions/${transactionId}/toggle-review`, {
            ...getCsrfFields(),
            _METHOD: 'PUT'
        })

        return {status, data}
    } catch ({response: {status, data: errors}}) {
        return {status, errors}
    }
}

export const uploadCsvTransactions = async (csvFile, parentDom) => {
    try {
        clearErrors(parentDom)

        const {status, data} = await axe.post(`/transactions/upload-from-csv`, {
            ...getCsrfFields(),
            csvFile,
        }, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        })

        return {status, data}
    } catch ({response: {status, data: errors}}) {
        handleErrors(errors, parentDom)
    }
}

export const deleteTransactionReceipt = async (transactionId, receiptId) => {
    const {status, data} = await axe.post(`/transactions/${transactionId}/receipts/${receiptId}`, {
        ...getCsrfFields(),
        _METHOD: 'DELETE'
    })

    return {status, data}
}

export const uploadTransactionReceipts = async (transactionId, receipts, parentDom) => {
    try {
        clearErrors(parentDom)

        const response = await axe.post(`/transactions/${transactionId}/receipts`, {
            ...getCsrfFields(),
            receipts: receipts,
            _METHOD: 'PUT'
        }, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        })

        return response
    } catch ({response: {status, data: errors}}) {
        handleErrors(errors, parentDom)
    }
}

export const updateTransaction = async (transactionId, transaction, parentDom) => {
    try {
        clearErrors(parentDom)

        const {status, data} = await axe.put(`/transactions/${transactionId}`, {
            ...transaction,
            ...getCsrfFields()
        })

        return {status, data}
    } catch ({response: {status, data: errors}}) {
        handleErrors(errors, parentDom)

        return {status, errors}
    }
}

export const getTransaction = async (transactionId, parentDom) => {
    try {
        clearErrors(parentDom)

        const {status, data} = await axe.get(`/transactions/${transactionId}`)
        return {status, data}
    } catch ({response: {status, data: errors}}) {
        return {status, errors}
    }
}

export const deleteTransaction = async (transactionId) => {
    await axe.post(`/transactions/${transactionId}`, {
        ...getCsrfFields()
    }, {
        headers: {
            'X-Http-Method-Override': 'DELETE'
        }
    });
}

export const createTransaction = async (transaction, parentDom) => {
    try {
        clearErrors(parentDom)

        const {status, data} = await axe.post(`/transactions`, {
            ...transaction,
            ...getCsrfFields(),
        })

        return {status, data}
    } catch ({response: {status, data: errors}}) {
        handleErrors(errors, parentDom)

        return {status, errors}
    }
}

/** Category requests */
export const getCategories = async () => {
    try {
        const {status, data} = await axe.get('/categories/list')

        return {status, data}
    } catch ({response}) {
        console.log(response)
    }
}

export const createCategory = async (name, domElement) => {
    try {
        clearErrors(domElement)

        const {status, data} = await axe.post(`/categories`, {
            name,
            ...getCsrfFields()
        })
        return {status, data}
    } catch ({response: {status, data: errors}}) {
        handleErrors(errors, domElement)

        return {status, errors}
    }
}

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
    } catch ({response: {status, data: errors}}) {
        handleErrors(errors, domElement)

        return {status, errors}
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

/** Error handlers and CSRF generator */
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

const handleErrors = (errors, domElement) => {
    for (const error in errors) {
        const input = domElement.querySelector(`[name="${error}"]`)
        const inputParent = input.parentElement
        input.classList.add('is-invalid')

        for (const message of errors[error]) {
            const errorDiv = `<div class="invalid-feedback">${message}</div>`

            inputParent.innerHTML += errorDiv
        }
    }
}

export const clearErrors = (domElement) => {
    const errorInputs = domElement.querySelectorAll('.is-invalid')
    const errorDivs = domElement.querySelectorAll('.invalid-feedback')

    for (const errorInput of errorInputs) {
        errorInput.classList.remove('is-invalid')
    }

    for (const errorDiv of errorDivs) {
        errorDiv.remove()
    }
}