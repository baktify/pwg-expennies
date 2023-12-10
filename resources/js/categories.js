import {Modal} from "bootstrap"
import {getCategory, updateCategory, deleteCategory} from "./requests";

const openEditCategoryModal = (modal, {id, name}) => {
    const nameInput = modal._element.querySelector('input[name="name"]')

    nameInput.value = name

    modal._element.querySelector('.save-category-btn').setAttribute('data-id', id)
    modal.show()
}

window.addEventListener('DOMContentLoaded', function () {
    const editCategoryModal = new Modal(document.getElementById('editCategoryModal'))
    const editCategoryButtons = document.querySelectorAll('.edit-category-btn')

    editCategoryButtons.forEach(button =>
        button.addEventListener('click', (event) => {
            const categoryId = event.currentTarget.getAttribute('data-id')

            getCategory(categoryId).then(
                data => openEditCategoryModal(editCategoryModal, data)
            )

        })
    )

    document.querySelector('.save-category-btn')
        .addEventListener('click', (event) => {
            const categoryId = event.currentTarget.getAttribute('data-id')
            const categoryName = editCategoryModal._element.querySelector('input[name="name"]').value

            updateCategory(categoryId, categoryName, editCategoryModal._element).then(data => {
                if (data.status === 200) {
                    editCategoryModal.hide()
                }
            })
        });

    document.querySelectorAll('.delete-category-btn').forEach(item => {
        item.addEventListener('click', (event) => {
            const categoryId = event.currentTarget.getAttribute('data-id')

            if (confirm(`Do you want to delete category ${categoryId}?`)) {
                deleteCategory(categoryId)
            }
        });
    })
})
