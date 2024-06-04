jQuery(document).ready(function ($) {
    if ($('.tla-use-case-search').length === 0) {
        return; // Exit if the container is not found
    }

    let model, totalVectors, useCases;

    // Disable the search button initially and change its text to 'Loading...'
    const searchButton = $('#tla-sear-search-button');
    searchButton.prop('disabled', true).text('Loading...');

    /**
     * Load the TensorFlow.js model and then load the use cases.
     */
    async function loadModel() {
        try {
            console.log("Loading model...");
            model = await use.load();
            console.log("Model loaded successfully.");
            await loadUseCases();
        } catch (error) {
            console.error("Error loading model:", error);
        } finally {
            searchButton.prop('disabled', false).text('Search');
            // Check if there's a query in the URL and perform the search
            const urlParams = new URLSearchParams(window.location.search);
            const query = urlParams.get('q');
            if (query) {
                $('#tla-sear-search-term').val(query);
                handleSearch();
            }
        }
    }

    /**
     * Fetch use cases from the database and encode their titles.
     */
    async function loadUseCases() {
        try {
            console.log("Fetching use cases...");
            const sentences = await getSentences();
            console.log("Use cases fetched:", sentences);

            const useCasesHash = hash(JSON.stringify(useCases));
            const cachedHash = localStorage.getItem('useCasesHash');
            const cachedVectors = JSON.parse(localStorage.getItem('totalVectors'));

            if (cachedHash === useCasesHash && cachedVectors) {
                console.log("Using cached encoded data.");
                totalVectors = cachedVectors;
            } else {
                totalVectors = await encodeSentences(sentences);
                localStorage.setItem('useCasesHash', useCasesHash);
                localStorage.setItem('totalVectors', JSON.stringify(totalVectors));
                console.log("Sentences encoded successfully and cached.");
            }
        } catch (error) {
            console.error("Error loading use cases or encoding sentences:", error);
        }
    }

    /**
     * Fetch use case titles from the REST API.
     * 
     * @return {Promise<Array>} Array of use case titles.
     */
    async function getSentences() {
        try {
            const response = await fetch(myCustomPlugin.apiUrl + '/use-cases', {
                headers: {
                    'X-WP-Nonce': myCustomPlugin.nonce
                }
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();
            useCases = data.map(useCase => ({
                id: useCase.id,
                title: useCase.use_case_title,
                slug: useCase.use_case_slug,
                thumbnail_url: useCase.use_case_thumbnail_url,
                description: useCase.use_case_description
            }));
            return useCases.map(useCase => useCase.title);
        } catch (error) {
            console.error("Error fetching use cases:", error);
            return [];
        }
    }

    /**
     * Encode an array of sentences using the TensorFlow.js model.
     * 
     * @param {Array} sentences Array of sentences to encode.
     * @return {Promise<Array>} Encoded vectors.
     */
    async function encodeSentences(sentences) {
        console.log("Encoding sentences...");
        const embeddings = await model.embed(sentences);
        return embeddings.arraySync();
    }

    /**
     * Encode a single sentence using the TensorFlow.js model.
     * 
     * @param {string} sentence Sentence to encode.
     * @return {Promise<Array>} Encoded vector.
     */
    async function encodeSentence(sentence) {
        console.log("Encoding sentence:", sentence);
        const embeddings = await model.embed([sentence]);
        return embeddings.arraySync()[0];
    }

    /**
     * Calculate cosine similarity between a matrix of vectors and a single vector.
     * 
     * @param {Array} matrix Matrix of vectors.
     * @param {Array} vector Single vector.
     * @return {Array} Array of similarity scores.
     */
    function cosineSimilarity(matrix, vector) {
        console.log("Calculating cosine similarity...");
        if (!matrix || !vector) {
            console.error("Invalid input to cosine similarity calculation.");
            return [];
        }
        const matrixTensor = tf.tensor2d(matrix);
        const vectorTensor = tf.tensor1d(vector);
        const normVectorTensor = vectorTensor.div(vectorTensor.norm());
        const normMatrixTensor = matrixTensor.div(matrixTensor.norm('euclidean', 1, true));
        const similarity = normMatrixTensor.matMul(normVectorTensor.reshape([-1, 1])).squeeze();
        return similarity.arraySync();
    }

    /**
     * Handle the click event for the search button.
     */
    function handleSearch() {
        const searchTerm = $('#tla-sear-search-term').val();
        if (!searchTerm || !model) {
            console.warn("Model not loaded or search term is empty.");
            return;
        }

        // Update the URL with the search query without reloading the page
        const newUrl = `${window.location.protocol}//${window.location.host}${window.location.pathname}?query=${encodeURIComponent(searchTerm)}`;
        window.history.pushState({ path: newUrl }, '', newUrl);

        searchButton.prop('disabled', true).text('Searching...');

        try {
            console.log("Processing search for:", searchTerm);
            encodeSentence(searchTerm).then(searchValueVector => {
                const result = cosineSimilarity(totalVectors, searchValueVector);

                const fullResult = result.map((score, index) => ({
                    id: useCases[index].id,
                    description: useCases[index].description,
                    slug: useCases[index].slug,
                    thumbnail_url: useCases[index].thumbnail_url,
                    score
                }));

                console.log("Full search results with probabilities:", fullResult);

                const sortedResult = fullResult
                    .sort((a, b) => b.score - a.score)
                    .slice(0, 4)
                    .map(item => ({
                        id: item.id,
                        description: item.description,
                        slug: item.slug,
                        thumbnail_url: item.thumbnail_url,
                        score: item.score
                    }));

                console.log("Top search results:", sortedResult);
                displayResults(sortedResult);
            }).catch(error => {
                console.error("Error processing search:", error);
            }).finally(() => {
                searchButton.prop('disabled', false).text('Search');
            });
        } catch (error) {
            console.error("Error processing search:", error);
            searchButton.prop('disabled', false).text('Search');
        }
    }

    /**
     * Display search results on the page.
     * 
     * @param {Array} results Array of search results.
     */
    function displayResults(results) {
        console.log("Displaying results...");
        const resultsContainer = $('#tla-sear-results-container');
        resultsContainer.empty();
        results.forEach(result => {
            const cardHtml = `
                <div class="tla-sear-card">
                    <a href="${myCustomPlugin.siteUrl}/usecase/${result.id}/${result.slug}">
                        <img src="${result.thumbnail_url}" alt="${result.description}">
                        <div class="tla-sear-card-title">${result.description}</div>
                    </a>
                </div>
            `;
            resultsContainer.append(cardHtml);
        });
    }

    /**
     * Create a simple hash from a string.
     * 
     * @param {string} str Input string.
     * @return {string} Hash of the input string.
     */
    function hash(str) {
        let hash = 0, i, chr;
        if (str.length === 0) return hash;
        for (i = 0; i < str.length; i++) {
            chr = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + chr;
            hash |= 0; // Convert to 32bit integer
        }
        return hash.toString();
    }

    // Handle Enter key press for search input
    $('#tla-sear-search-term').on('keypress', function (e) {
        if (e.which === 13) {
            handleSearch();
        }
    });
    searchButton.on('click', handleSearch);

    // Load the model when the page is ready
    loadModel();
});
