import DataTable from 'datatables.net';
import {Modal} from 'bootstrap'
import {
    getCategories,
    createTransaction,
    deleteTransaction,
    getTransaction,
    updateTransaction
} from "./requests";

document.addEventListener('DOMContentLoaded', function () {
    let categories = [];

    const createTransactionModal = new Modal('#createTransactionModal')
    const createTransactionForm = document.forms.createTransaction
    const editTransactionModal = new Modal('#editTransactionModal')
    const editTransactionForm = document.forms.editTransaction
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

    const onDeleteTransaction = (event) => {
        const deleteBtn = event.target.closest('.delete-category-btn')

        if (deleteBtn) {
            const transactionId = deleteBtn.getAttribute('data-id')

            if (confirm('Are you sure to delete transaction with id ' + transactionId)) {
                deleteTransaction(transactionId).then(() => table.draw())
            }
        }
    }

    const onEditTransaction = (event) => {
        const editBtn = event.target.closest('.edit-category-btn')

        if (editBtn) {
            const transactionId = editBtn.getAttribute('data-id')

            getTransaction(transactionId, editTransactionModal._element)
                .then(({status, data}) => {
                    if (status === 200) {
                        editTransactionModal.show()
                        fillEditTransactionModalWithData(data, transactionId)
                    }
                })
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
            // {data: 'amount'},
            {
                data: (row) => new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD'
                }).format(row.amount)
            },
            {data: 'user'},
            {data: 'category'},
            {data: 'createdAt'},
            {data: 'updatedAt'},
            {
                sortable: false,
                data: (transaction) => `
                    <div class="d-flex">
                        <button class="ms-2 btn btn-outline-primary delete-category-btn" data-id="${transaction.id}">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                        <button class="ms-2 btn btn-outline-primary edit-category-btn" data-id="${transaction.id}">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                    </div>
                `
            }
        ]
    })

    /** New transaction request */
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

    /** Delete transaction request */
    transactionsTable.addEventListener('click', onDeleteTransaction)

    /** Edit transaction request */
    transactionsTable.addEventListener('click', onEditTransaction)

    /** Update transaction request */
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
});
