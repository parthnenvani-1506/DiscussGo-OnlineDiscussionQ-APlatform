document.addEventListener('DOMContentLoaded', function () {
    const categoryLinks = document.querySelectorAll('.category-link');
    const questionsContainer = document.getElementById('questions-container');
    const headingTitle = document.getElementById('questions-heading');

    const getParam = (key) => new URLSearchParams(window.location.search).get(key);

    categoryLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            const categoryId = this.getAttribute('data-category-id');
            const newParams = new URLSearchParams();

            if (categoryId) newParams.set('c-id', categoryId);
            if (getParam('latest')) newParams.set('latest', 'true');
            if (getParam('u-id')) newParams.set('u-id', getParam('u-id'));

            window.history.pushState({}, '', '?' + newParams.toString());

            categoryLinks.forEach(l => l.classList.remove('active-category'));
            this.classList.add('active-category');

            if (headingTitle) {
                if (getParam('latest')) headingTitle.textContent = 'Latest Questions';
                else if (getParam('u-id')) headingTitle.textContent = 'My Questions';
                else headingTitle.textContent = 'Questions';
            }

            fetch('./client/questions_partial.php?' + newParams.toString())
                .then(response => response.text())
                .then(html => {
                    if (questionsContainer) {
                        questionsContainer.innerHTML = html;
                    }
                })
                .catch(error => console.error('Error fetching questions:', error));
        });
    });
});
