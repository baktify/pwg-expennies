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

            // TODO: Post update to the category
            console.log(categoryId)
        })
})

const openEditCategoryModal = (modal, {id, name})  => {
    const nameInput = modal._element.querySelector('input[name="name"]')

    nameInput.value = name

    modal._element.querySelector('.save-category-btn').setAttribute('data-id', id)
    modal.show()
}