import DataTable from 'datatables.net';
import {Modal} from 'bootstrap'
import {
    getCategories,
    createTransaction,
    deleteTransaction,
    getTransaction,
    updateTransaction,
    uploadTransactionReceipts
} from "./requests";

document.addEventListener('DOMContentLoaded', function () {
    let categories = [];

    const createTransactionModal = new Modal('#createTransactionModal')
    const editTransactionModal = new Modal('#editTransactionModal')
    const uploadTransactionReceiptsModal = new Modal('#uploadReceiptsModal')
    const createTransactionForm = document.forms.createTransaction
    const editTransactionForm = document.forms.editTransaction
    const uploadTransactionReceiptsForm = document.forms.uploadTransactionReceipts
    const createTransactionCategorySelectInput = createTransactionForm.elements.categoryId
    const editTransactionCategorySelectInput = editTransactionForm.elements.categoryId
    const transactionsTable = document.getElementById('transactionsTable')

    const fillSelectInputWithCategories = (selectInput, categories) => {
        for (const {id, name} of categories) {
            const option = `<option value="${id}">${name}</option>`
            selectInput.innerHTML += option
        }
    }

    const fillEditTransactionModalWithData = (data) => {
        const {
            id: idInput,
            categoryId: categoryInput,
            description: descriptionInput,
            amount: amountInput,
            date: dateInput
        } = editTransactionForm.elements

        idInput.setAttribute('value', data.id)
        categoryInput.value = data.category.id
        descriptionInput.value = data.description
        amountInput.value = data.amount
        dateInput.value = data.date
    }

    const onClickTransactionDelete = (event) => {
        const deleteBtn = event.target.closest('.delete-transaction-btn')

        if (deleteBtn) {
            const transactionId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure to delete transaction with id ' + transactionId)) {
                deleteTransaction(transactionId).then(() => table.draw())
            }
        }
    }

    const onClickTransactionEdit = (event) => {
        const editBtn = event.target.closest('.edit-transaction-btn')

        if (editBtn) {
            const transactionId = editBtn.getAttribute('data-id')

            getTransaction(transactionId, editTransactionModal._element).then(({status, data}) => {
                if (status === 200) {
                    editTransactionModal.show()
                    fillEditTransactionModalWithData(data, transactionId)
                }
            })
        }
    }

    const onClickTransactionReceiptsUpload = (event) => {
        const uploadBtn = event.target.closest('.upload-transaction-receipts-btn')

        if (uploadBtn) {
            const transactionId = uploadBtn.getAttribute('data-id')
            uploadTransactionReceiptsForm.querySelector('[type="submit"]').setAttribute('data-id', transactionId)

            uploadTransactionReceiptsModal.show()
        }
    }

    /** Getting categories on page load */
    getCategories().then(({status, data}) => {
        if (status === 200) {
            categories = [...data]

            fillSelectInputWithCategories(createTransactionCategorySelectInput, categories)
            fillSelectInputWithCategories(editTransactionCategorySelectInput, categories)
        }
    })

    /** Datatable hydration */
    const table = new DataTable('#transactionsTable', {
        serverSide: true,
        ajax: '/transactions/load',
        orderMulti: false,
        columns: [
            {data: 'description'},
            {data: 'date'},
            {
                data: (row) => new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD'
                }).format(row.amount)
            },
            {data: 'category'},
            {
                data: ({id: transactionId, receipts}) => {
                    let icons = []

                    for (const {id, name} of receipts) {
                        const span = `
                            <span class="position-relative">
                                <a href="/transactions/${transactionId}/receipts/${id}" target="_blank" title="${name}">
                                    <i class="bi bi-file-earmark-text download-receipt text-primary fs-4"></i>
                                </a>
                                <i class="bi bi-x-circle-fill delete-receipt text-danger"
                                    style="position: absolute; bottom: 10px; right: -5px;"
                                    role="button"
                                    data-id="${id}" 
                                    data-transactionId="${transactionId}">
                                </i>
                            </span>
                        `
                        icons = [...icons, span]
                    }

                    return icons.join('')
                }
            },
            // {data: 'createdAt'},
            // {data: 'updatedAt'},
            {
                sortable: false,
                data: (transaction) => `
                    <div class="d-flex">
                        <button class="ms-2 btn btn-outline-primary delete-transaction-btn" data-id="${transaction.id}">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                        <button class="ms-2 btn btn-outline-primary edit-transaction-btn" data-id="${transaction.id}">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <button class="ms-2 btn btn-outline-primary upload-transaction-receipts-btn" data-id="${transaction.id}">
                            <i class="bi bi-upload"></i>
                        </button>
                    </div>
                `
            }
        ]
    })

    transactionsTable.addEventListener('click', onClickTransactionDelete)

    transactionsTable.addEventListener('click', onClickTransactionEdit)

    transactionsTable.addEventListener('click', onClickTransactionReceiptsUpload)

    createTransactionForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const formdata = new FormData(event.target)
        const transaction = Object.fromEntries(
            formdata.entries()
        )

        createTransaction(transaction, createTransactionModal._element).then(response => {
            if (response.status === 200) {
                createTransactionModal.hide()
                createTransactionForm.reset()
                table.draw()
            }
        })
    })

    editTransactionForm.addEventListener('submit', (event) => {
        event.preventDefault()

        const transactionId = editTransactionForm.elements.id.value

        const formData = new FormData(event.target)
        const transaction = Object.fromEntries(
            formData.entries()
        )

        updateTransaction(transactionId, transaction, editTransactionModal._element).then(({status, data}) => {
            if (status === 200) {
                editTransactionModal.hide()
                table.draw()
            }
        })
    })

    uploadTransactionReceiptsForm.addEventListener('submit', (event) => {
        event.preventDefault()

        const transactionId = uploadTransactionReceiptsForm.elements.submit.getAttribute('data-id')
        const receipts = uploadTransactionReceiptsForm.elements.receipts.files

        uploadTransactionReceipts(
            transactionId, receipts, uploadTransactionReceiptsModal._element
        ).then(({status, data}) => {
            if (status === 200) {
                table.draw()
                uploadTransactionReceiptsModal.hide()
            }
        })
    })
});
