import {Modal} from "bootstrap"
import {getCategory, updateCategory} from "./requests";

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

            updateCategory(categoryId, categoryName).then(
                json => console.log(json)
            )
        });

})
