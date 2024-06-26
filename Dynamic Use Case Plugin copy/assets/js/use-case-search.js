jQuery(document).ready(function ($) {
    console.log("Document is ready.");

    if ($('.tla-use-case-search').length === 0) {
        console.log("Search container not found.");
        return; // Exit if the container is not found
    }

    const searchButton = $('#tla-sear-search-button');
    const searchTermInput = $('#tla-sear-search-term');
    const resultsContainer = $('#tla-sear-results-container');

    if (!searchButton.length || !searchTermInput.length || !resultsContainer.length) {
        console.log("Required elements are not found.");
        return; // Exit if the required elements are not found
    }

    function handleSearch() {
        const searchTerm = searchTermInput.val();
        console.log("Search button clicked. Search term:", searchTerm);

        if (!searchTerm) {
            console.warn("Search term is empty.");
            return;
        }

        // Update the URL with the search query parameter
        window.location.replace(`${window.location.pathname}?q=${searchTerm}`);
    }

    async function fetchSearchResults(searchTerm) {
        console.log("Calling API with search term:", searchTerm);
        const apiUrl = 'https://search.thelenders.app:5000/find_similar';
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                text: searchTerm,
                user_id: 'Ketul'
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        console.log("API response received");
        return await response.json();
    }

    async function fetchUseCases(serialNumbers) {
        console.log("Fetching use cases for serial numbers:", serialNumbers);
        const response = await fetch(myCustomPlugin.apiUrl + '/use-cases', {
            headers: {
                'X-WP-Nonce': myCustomPlugin.nonce
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        const useCases = data.map(useCase => ({
            id: useCase.id,
            title: useCase.use_case_title,
            slug: useCase.use_case_slug,
            thumbnail_url: useCase.use_case_thumbnail_url,
            description: useCase.use_case_description,
            usecase_url: useCase.use_case_url,
            score: useCase.score // Assuming 'score' is part of the use case data
        }));

        return useCases;
    }

    function displayResults(results) {
        console.log("Displaying results...");
        resultsContainer.empty();
        results.forEach(result => {
            const cardHtml = `
                <div class="tla-sear-card">
                    <a href="${result.usecase_url}">
                        <img src="${result.thumbnail_url}" alt="${result.title},${result.description}">
                        <div class="tla-sear-card-title">${result.description}</div>
                    </a>
                </div>
            `;
            resultsContainer.append(cardHtml);
        });
    }

    function getQueryParam(param) {
        let urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    function performSearchFromURL() {
        let query = getQueryParam('q');
        console.log("Query from URL:", query);

        if (query) {
            searchTermInput.val(query);
            let cachedResults = localStorage.getItem(`searchResults_${query}`);
            let cachedTimestamp = localStorage.getItem(`searchTimestamp_${query}`);

            if (cachedResults && cachedTimestamp) {
                let now = Date.now();
                if (now - parseInt(cachedTimestamp) < 3600000) { // 1 hour
                    console.log("Using cached results.");
                    displayResults(JSON.parse(cachedResults));
                    return;
                } else {
                    localStorage.removeItem(`searchResults_${query}`);
                    localStorage.removeItem(`searchTimestamp_${query}`);
                }
            }

            fetchSearchResults(query).then(result => {
                console.log("API result received:", result);

                if (!result["Serial Number"] || !result.data) {
                    console.error("Unexpected API response format:", result);
                    return;
                }

                const serialNumbers = result["Serial Number"].map(String);
                const data = JSON.parse(result.data);

                fetchUseCases(serialNumbers).then(useCases => {
                    console.log("Use cases fetched:", useCases);
                    const filteredUseCases = serialNumbers
                        .map(number => useCases.find(useCase => useCase.id === number))
                        .filter(Boolean)
                        .slice(0, 4); // Limit to 4 results

                    console.log("Top search results:", filteredUseCases);
                    localStorage.setItem(`searchResults_${query}`, JSON.stringify(filteredUseCases));
                    localStorage.setItem(`searchTimestamp_${query}`, Date.now().toString());

                    displayResults(filteredUseCases);
                });
            });
        }
    }

    searchTermInput.on('keypress', function (e) {
        if (e.which === 13) {
            handleSearch();
        }
    });

    searchButton.on('click', handleSearch);

    performSearchFromURL();
});
