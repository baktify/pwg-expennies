import {Modal} from "bootstrap"

window.addEventListener('DOMContentLoaded', function () {
    const editCategoryModal = new Modal(document.getElementById('editCategoryModal'))
    const editCategoryButtons = document.querySelectorAll('.edit-category-btn')

    editCategoryButtons.forEach(button =>
        button.addEventListener('click', (event) => {
            const categoryId = event.currentTarget.getAttribute('data-id')

            fetch(`/categories/${categoryId}`)
                .then(response => response.json())
                .then(json => openEditCategoryModal(editCategoryModal, json))
        })
    )

    document.querySelector('.save-category-btn')
        .addEventListener('click', (event) => {
            const categoryId = event.currentTarget.getAttribute('data-id')
            const categoryName = editCategoryModal._element.querySelector('input[name="name"]').value

            const makeRequest = async () => {
                const response = await fetch(`/categories/${categoryId}`, {
                    method: 'POST',
                    body: JSON.stringify({
                        name: categoryName,
                        ...getCsrfFields()
                    }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })

                console.log(await response.json())
            }
            makeRequest();
        });
})

const openEditCategoryModal = (modal, {id, name}) => {
    const nameInput = modal._element.querySelector('input[name="name"]')

    nameInput.value = name

    modal._element.querySelector('.save-category-btn').setAttribute('data-id', id)
    modal.show()
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