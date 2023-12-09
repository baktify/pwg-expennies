window.addEventListener('DOMContentLoaded', () => {
    const editCategoryButtons = document.querySelectorAll('.edit-category-btn')

    editCategoryButtons.forEach(button => button.addEventListener('click', (event) => {
        const categoryId = event.currentTarget.getAttribute('data-id')

        // TODO
        console.log(categoryId)
    }))
})