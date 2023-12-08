window.addEventListener('DOMContentLoaded', () => {
    const editCategoryButtons = document.querySelectorAll('.edit-category-btn')

    const editCategoryButtonHandler = (event) => {
        const categoryId = event.currentTarget.getAttribute('data-id')

        // TODO
        console.log(categoryId)
    }

    editCategoryButtons.forEach(button=> button.addEventListener('click', editCategoryButtonHandler))
})