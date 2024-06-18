// jQuery(document).ready(function ($) {
//     /**
//      * Fetch a single use case by ID from the REST API.
//      * 
//      * @param {int} useCaseId The ID of the use case to fetch.
//      */
//     async function getSingleUseCase(useCaseId) {
//         try {
//             // Make an API request to fetch the use case data
//             const response = await fetch(`${myCustomPlugin.apiUrl}/use-case/${useCaseId}`, {
//                 headers: {
//                     'X-WP-Nonce': myCustomPlugin.nonce
//                 }
//             });
//             if (!response.ok) {
//                 throw new Error(`HTTP error! status: ${response.status}`);
//             }
//             const data = await response.json();
//             displayUseCase(data);
//         } catch (error) {
//             console.error("Error fetching use case:", error);
//         }
//     }

//     /**
//      * Display the use case details on the page.
//      * 
//      * @param {object} useCase The use case data to display.
//      */
//     function displayUseCase(useCase) {
//         // Populate the use case details on the page
//         $('#use-case-title').text(useCase.use_case_title);
//         $('#use-case-description').text(useCase.use_case_description);
//         $('#use-case-solution').text(useCase.use_case_solution_text);
//         $('#use-case-result').text(useCase.use_case_result_text);
//         $('#use-case-thumbnail').attr('src', useCase.use_case_thumbnail_url);
//         $('#use-case-image').attr('src', useCase.use_case_image_url);
//         $('#use-case-cta').text(useCase.use_case_cta_text).attr('href', useCase.use_case_cta_url);
//     }

//     // Fetch the use case ID from the URL
//     const urlParams = new URLSearchParams(window.location.search);
//     const useCaseId = urlParams.get('id');
    
//     // If a use case ID is found in the URL, fetch and display the use case
//     if (useCaseId) {
//         getSingleUseCase(useCaseId);
//     }
// });
