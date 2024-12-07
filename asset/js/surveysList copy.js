// // selectAll
// const selectAllCheckbox = document.getElementById('selectAll');
// const tdCheckboxes = document.querySelectorAll('td input[type="checkbox"]');

// selectAllCheckbox.addEventListener('change', function() {
//     console.log('ok')
//     tdCheckboxes.forEach(checkbox => {z
//         checkbox.checked = selectAllCheckbox.checked;
//     });
// });

// // fetch
// document.getElementById('surveyForm').addEventListener('submit', function(e) {
//     e.preventDefault();
//     let selectedRows = [];
    
//     document.querySelectorAll('input[name="survey_id[]"]:checked').forEach(checkbox => {
//         let row = checkbox.closest('tr');
//         let rowData = {
//             id: checkbox.value,
//             site: row.children[1].textContent.trim(),
//             name: row.children[2].textContent.trim(),
//             date_captured: row.children[3].textContent.trim(),
//             organization_id: row.children[4].textContent.trim(),
//             site_id: row.children[5].textContent.trim()
//         };
//         selectedRows.push(rowData);
//     });

//     fetch('/surveysList/download', {
//         method: 'POST',
//         headers: {
//             'Content-Type': 'application/json'
//         },
//         body: JSON.stringify(selectedRows)
//     })
//     .then(response => response.json())
//     .then(data => {
//         console.log('Success:', data);
//     })
//     .catch(error => {
//         console.error('Error:', error);
//     });
// });

// // Crée la barre de progression
// const progressBar = document.createElement('div');
// progressBar.className = 'progress-container';
// progressBar.innerHTML = `
//     <div class="progress-bar">
//         <div class="progress-fill"></div>
//     </div>
//     <div class="progress-text">En attente...</div>
// `;
// document.body.appendChild(progressBar);

// // Ajoute le style CSS
// const style = document.createElement('style');
// style.textContent = `
//     .progress-container {
//         width: 100%;
//         max-width: 500px;
//         margin: 20px auto;
//         display: none;
//     }
//     .progress-bar {
//         width: 100%;
//         height: 20px;
//         background: #f0f0f0;
//         border-radius: 10px;
//         overflow: hidden;
//     }
//     .progress-fill {
//         width: 0%;
//         height: 100%;
//         background: #4CAF50;
//         transition: width 0.3s ease;
//     }
//     .progress-text {
//         margin-top: 10px;
//         text-align: center;
//         font-size: 14px;
//     }
// `;
// document.head.appendChild(style);

// let progressInterval;


// // Affiche et réinitialise la barre de progression
// progressBar.style.display = 'block';
// const progressFill = progressBar.querySelector('.progress-fill');
// const progressText = progressBar.querySelector('.progress-text');
// progressFill.style.width = '0%';
// progressText.textContent = 'Démarrage...';

    // // Démarre la vérification de la progression
    // progressInterval = setInterval(async () => {
    //     try {
    //         const response = await fetch('/surveysList/checkProgress');
    //         const progress = await response.json();
            
    //         if (progress.percent !== null) {
    //             progressFill.style.width = progress.percent + '%';
    //         }
    //         if (progress.message) {
    //             progressText.textContent = progress.message;
    //         }
            
    //         // Si terminé ou erreur, arrête la vérification
    //         if (progress.percent === 100 || progress.percent === -1) {
    //             clearInterval(progressInterval);
    //         }
    //     } catch (error) {
        //         console.error('Erreur de vérification de la progression:', error);
        //     }
        // }, 1000);
        
        document.getElementById('surveyForm').addEventListener('submit', async () => {
            const urls = [
                'https://srv-01-eu-west-1.data.propelleraero.com/ob1a160b21/pqb8fa95b4_site_dsm.tiff?Policy=eyJTdGF0ZW1lbnQiOlt7IlJlc291cmNlIjoiaHR0cHM6Ly8qL29iMWExNjBiMjEvKiIsIkNvbmRpdGlvbiI6eyJEYXRlTGVzc1RoYW4iOnsiQVdTOkVwb2NoVGltZSI6MTczMzgzODk5Mn19fV19&Signature=xa8aWlOpfsbe3c4zqFz9L4Y35jcSmXXKxW2VIb-E0-U286BtQ2ROcNyD1ghku~Ga~TN06nxNj~ZpyegQotLevHgSyg8t1MzW5VRl8xS-WT8xQ6Mpum9D2FA6PwQbqAXr1kFxVbNkWE3oAUgzehFhIR6OiiLnImabye~RqJ5btmS6Hhrcwpm4TiJVm1YUxzNVGhX4WPncwTrsDZl3UUU-olHAVR8PxqKBxb3WX5NWDqccviwyH1ZIQwQdpqNSWJlvJPL9NHRFxv-GRS14t6JmBqAl38Hxq9e5ZwuRluqz0ViiYCSScmCmBR7wU9qFZFkmPFUzT4tdFeMMwH1OAtjzDg__&Key-Pair-Id=APKAI6AKZOWIA7VNVEUQ&response-content-disposition=attachment&response-cache-control=max-age%3D31536000',
                'https://srv-01-eu-west-1.data.propelleraero.com/obde1febc2/002_pqb8fa95b4-DSM.tiff?Policy=eyJTdGF0ZW1lbnQiOlt7IlJlc291cmNlIjoiaHR0cHM6Ly8qL29iZGUxZmViYzIvKiIsIkNvbmRpdGlvbiI6eyJEYXRlTGVzc1RoYW4iOnsiQVdTOkVwb2NoVGltZSI6MTczMzgzODk5Mn19fV19&Signature=TFoQn7XclhntNC1TD6ALj6KaywkjIafYQPje44-z2RD1U2gnUJJcZMstBPDiihmVh7ha7sJTyVj3Xukf3JQwjWYclB3HE6LMCbfP4MLRgc6XPq3xWtDF-YWJew6hRxK-qAmCheUOG~5BNXFzS2g3akW3OPbGMGRLJTYJNRNRwpOrswRCMnQBEHcry5VMa9pIX3ViIIuuIFFUAAuOIYipsqr4wDd42oIpk3R0lmLhcDEAhCy5MbxLD1QF5r1syjpxMZFi05VpKQA-RE59lineU5O27e~gdXHy53PtvLGIgpqm-W4ewd8trjNs6QAMESRe1JWerLUSb0ulOFwSy1GNug__&Key-Pair-Id=APKAI6AKZOWIA7VNVEUQ&response-content-disposition=attachment&response-cache-control=max-age%3D31536000',
                'https://srv-01-eu-west-1.data.propelleraero.com/obf9ab184e/000_pqb8fa95b4-ortho.tiff?Policy=eyJTdGF0ZW1lbnQiOlt7IlJlc291cmNlIjoiaHR0cHM6Ly8qL29iZjlhYjE4NGUvKiIsIkNvbmRpdGlvbiI6eyJEYXRlTGVzc1RoYW4iOnsiQVdTOkVwb2NoVGltZSI6MTczMzgzODk5Mn19fV19&Signature=kZObEXfm3WzdkASaAEXAOwlT1SsVdMRxXEoJ8oCv4no3v6SATgIcueuTeHAPaWJ0LUSzUr3IjJfXAKwEdPvPJcss4SJSJytwNzyyvmjaurhMhmWwRghFOHpAvhRUuEOKJJxwWOuCseVYogAjeCJmt4nuS0Ukni6boatN~ROL53OeeL7v-gYhnEPiyApdNLKFQ~bSrxaqQZETLREq~VLXn1uwAhFdlM4nS1-iM5riWpI82uSwTZj59pHUhQj~VA2khDSM6O7dVwlu87HCZbo9c5U9nN5DzyfNWv4N456ViX3hR2t-BzXpNbMefLgWC1yjOJEyDzF6g1XQeT3Q0gfTSA__&Key-Pair-Id=APKAI6AKZOWIA7VNVEUQ&response-content-disposition=attachment&response-cache-control=max-age%3D31536000',
                'https://srv-01-eu-west-1.data.propelleraero.com/obb84f1a97/pqb8fa95b4_site_ortho.tiff?Policy=eyJTdGF0ZW1lbnQiOlt7IlJlc291cmNlIjoiaHR0cHM6Ly8qL29iYjg0ZjFhOTcvKiIsIkNvbmRpdGlvbiI6eyJEYXRlTGVzc1RoYW4iOnsiQVdTOkVwb2NoVGltZSI6MTczMzgzODk5Mn19fV19&Signature=y2OEUHoA9pHm1ecViycWJBBXXeNeLOWpcgUFhmL85xpXjQjlhvhLGnmyCoaaBMdBQpnNLMvLGCCrvoznlyNicX7rSESbLVR40Awk0TgNcYU1ZMfZ165smD3fdXa6vO38EIFAg0LK5lk5eR1u146jraTiX2SccEtoltWgvPsdNCAt~kVH2~gN3625WtQc-pnVOdKW8vRKfx4BWyN-PshSrddnLI-ItQQicEsa08kAIEYYIYYnx4ItSeJ3nWfVc4IC4g-qHX4ovQVibaIRnPsJcDGFOv8mhXinclbxctgecaWa9onm2QbhC4V33hKfIkNrjHanX3v8edYoJrSVArqzQg__&Key-Pair-Id=APKAI6AKZOWIA7VNVEUQ&response-content-disposition=attachment&response-cache-control=max-age%3D31536000',
                'https://srv-01-eu-west-1.data.propelleraero.com/obfb2b3496/805367e0-37bd-45fe-b1e5-04de26af9e38.laz?Policy=eyJTdGF0ZW1lbnQiOlt7IlJlc291cmNlIjoiaHR0cHM6Ly8qL29iZmIyYjM0OTYvKiIsIkNvbmRpdGlvbiI6eyJEYXRlTGVzc1RoYW4iOnsiQVdTOkVwb2NoVGltZSI6MTczMzgzODk5Mn19fV19&Signature=nboODBz7m8L9JP5kKFJ1Swl4bbqNaRTXHOXz4RbVHyYWOVi1C6IiBH9iz5fuWrJntBshY7LFRF3ZPMUiQhORmuycxWI839ZNvoqFyEbZOSOYc-g~8CDRFIf3jKbe-PgPKuKagkI3Vum5f9lQG3n44T7EorEmF7gBL-UMrNxCF-kgwQWAKF050pCSvZMtjPupl5uP4fxeUiQJcjLxAmMaVqQc6LoLDKxJ~MbKR6arnn3ITdnxrg0i8yepm5UbRjYyF6ah3HpXKcepoumFuoT5fOpFu~dMe~0tX5q0RLoPoSAajdJNz9V3PM3UO4sWGiOWKfertEscnBq5lAlFYiMEoQ__&Key-Pair-Id=APKAI6AKZOWIA7VNVEUQ&response-content-disposition=attachment&response-cache-control=max-age%3D31536000',
                'https://srv-01-eu-west-1.data.propelleraero.com/ob0af00503/b6bbbd7b-9384-48e4-af4a-6a4e85714556.laz?Policy=eyJTdGF0ZW1lbnQiOlt7IlJlc291cmNlIjoiaHR0cHM6Ly8qL29iMGFmMDA1MDMvKiIsIkNvbmRpdGlvbiI6eyJEYXRlTGVzc1RoYW4iOnsiQVdTOkVwb2NoVGltZSI6MTczMzgzODk5Mn19fV19&Signature=k0Dy4ag4S8pP~YMaypboSqd8XLxYT1Chjd6kFBOcJK-M5QbMDXi65jjEPsix49gpfvuh4FKokTtjsw3YzywDL4CWJC46E1CRT1DuKc7Nq6j1MYxEa6s42tal0Mra4Ag2L3vJ1Oe6mr-BAgUftINbHcwki0JsIQ1WcQhXBgkjfV~SGfDAE76xcl4SJ7myUsFthk3YkxH3SGmVLiGHCR6aJIOPl3rqMY5hZP1agWEYon5EU1Q4fLMDH~SNWwZbfBawaqbAr4DFkZkiDeEq6~rxUZ9XL0wA4G-LOd8sU9uPXwreXbv6CrZTZt33WhlKUzIN4OKHXaBmZ0pa0V0go0-ouQ__&Key-Pair-Id=APKAI6AKZOWIA7VNVEUQ&response-content-disposition=attachment&response-cache-control=max-age%3D31536000',
                'https://srv-01-eu-west-1.data.propelleraero.com/ob6b868501/f2ab5283-eeda-42dd-b859-6c8f3a72703f.laz?Policy=eyJTdGF0ZW1lbnQiOlt7IlJlc291cmNlIjoiaHR0cHM6Ly8qL29iNmI4Njg1MDEvKiIsIkNvbmRpdGlvbiI6eyJEYXRlTGVzc1RoYW4iOnsiQVdTOkVwb2NoVGltZSI6MTczMzgzODk5Mn19fV19&Signature=mlyxkjgi1rWttQJt8UrRHk999ISgbkbqKRnB1JHjoVThxhbskWI6z9mo39XSKU9kXvTvUtGKiqLdM5Xqs-3IX-k4TY8XropepLyCyM5b0TapJjNMFhFLr0tHqw3hGD-~Ni1FaYNg6H0fO4X~lZdJm2KKBPVdZHX7ih0ET~hMUeJZwNRw0UxG2VivC4bpcYoqkLRS7VNXD67vE99nqnJmeWsLYtqWfNH6Uk7cOiVcFMvfcagoWixaeYvgVXj-o72mNYlOU14IWE3ojEZMlAsMmbblJ7ETgboJtOW3D1s8G~gd2YQWB0E3ai-Y~zqSrXjYk2JKEGqp-CLFLmXLQvUqig__&Key-Pair-Id=APKAI6AKZOWIA7VNVEUQ&response-content-disposition=attachment&response-cache-control=max-age%3D31536000'
        
            ];
        
        try {
            const response = await fetch('/surveysList/download', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'createZip',
                    urls: urls
                })
            });
        }

//         clearInterval(progressInterval);

//         if (response.ok) {
//             const blob = await response.blob();
//             const downloadUrl = window.URL.createObjectURL(blob);
//             const a = document.createElement('a');
//             a.href = downloadUrl;
//             a.download = 'scanner3d_files.zip';
//             document.body.appendChild(a);
//             a.click();
//             window.URL.revokeObjectURL(downloadUrl);
//             document.body.removeChild(a);
            
//             // Nettoie le fichier de progression
//             await fetch('votre_script.php?action=checkProgress&cleanup=true');
            
//             setTimeout(() => {
//                 progressBar.style.display = 'none';
//             }, 3000);
//         } else {
//             progressText.textContent = 'Erreur lors du téléchargement';
//         }
//     } catch (error) {
//         clearInterval(progressInterval);
//         progressText.textContent = 'Erreur: ' + error.message;
//         console.error('Erreur:', error);
//     }
// });