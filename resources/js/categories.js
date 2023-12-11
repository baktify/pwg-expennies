import {Modal} from "bootstrap"
import {getCategory, updateCategory, deleteCategory} from "./requests";
import DataTable from "datatables.net"

const openEditCategoryModal = (modal, {id, name}) => {
    const nameInput = modal._element.querySelector('input[name="name"]')

    nameInput.value = name

    modal._element.querySelector('.save-category-btn').setAttribute('data-id', id)
    modal.show()
}

window.addEventListener('DOMContentLoaded', function () {
    const editCategoryModal = new Modal(document.getElementById('editCategoryModal'))
    // const editCategoryButtons = document.querySelectorAll('.edit-category-btn')

    const table = new DataTable('#categoriesTable', {
        serverSide: true,
        ajax: '/categories/load',
        orderMulti: false,
        columns: [
            {data: 'name'},
            {data: 'createdAt'},
            {data: 'updatedAt'},
            {
                sortable: false,
                data: row => `
                    <div class="d-flex">
                        <button class="ms-2 btn btn-outline-primary delete-category-btn" data-id="${row.id}">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                        <button class="ms-2 btn btn-outline-primary edit-category-btn" data-id="${row.id}">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                    </div>
                `
            }
        ]
    })

    document.querySelector('#categoriesTable').addEventListener('click', (event) => {
        const editBtn   = event.target.closest('.edit-category-btn')
        const deleteBtn = event.target.closest('.delete-category-btn')

        if (editBtn) {
            const categoryId = editBtn.getAttribute('data-id')

            getCategory(categoryId).then(
                data => openEditCategoryModal(editCategoryModal, data)
            )
        } else {
            const categoryId = deleteBtn.getAttribute('data-id')

            if (confirm(`Do you want to delete category ${categoryId}?`)) {
                deleteCategory(categoryId)
            }
        }
    })

    // editCategoryButtons.forEach(button =>
    //     button.addEventListener('click', (event) => {
    //         const categoryId = event.currentTarget.getAttribute('data-id')
    //
    //         getCategory(categoryId).then(
    //             data => openEditCategoryModal(editCategoryModal, data)
    //         )
    //
    //     })
    // )

    document.querySelector('.save-category-btn')
        .addEventListener('click', (event) => {
            const categoryId = event.currentTarget.getAttribute('data-id')
            const categoryName = editCategoryModal._element.querySelector('input[name="name"]').value

            updateCategory(categoryId, categoryName, editCategoryModal._element).then(data => {
                if (data.status === 200) {
                    table.draw()
                    editCategoryModal.hide()
                }
            })
        });

    // document.querySelectorAll('.delete-category-btn').forEach(item => {
    //     item.addEventListener('click', (event) => {
    //         const categoryId = event.currentTarget.getAttribute('data-id')
    //
    //         if (confirm(`Do you want to delete category ${categoryId}?`)) {
    //             deleteCategory(categoryId)
    //         }
    //     });
    // })
})
