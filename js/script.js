document.addEventListener('DOMContentLoaded', () => {
    const commentsDiv = document.getElementById('comments');
    const commentInput = document.getElementById('commentInput');
    const submitCommentBtn = document.getElementById('submitComment');

    // Función para crear un elemento de comentario/respuesta
    const createCommentElement = (comment) => {
        const commentElement = document.createElement('div');
        commentElement.classList.add('comment');
        // Añadir una clase diferente si es una respuesta para estilos
        if (comment.parent_comment_id !== null) {
            commentElement.classList.add('reply');
        }
        commentElement.dataset.commentId = comment.id_comentario; // Guardar el ID para respuestas

        let replyButtonHtml = '';
        // Solo mostrar el botón de responder si el usuario está logueado
        // y si el elemento userInfo (que se muestra cuando hay sesión) está visible
        const userInfoDiv = document.getElementById('userInfo');
        if (userInfoDiv && window.getComputedStyle(userInfoDiv).display !== 'none') {
            replyButtonHtml = `<button class="reply-btn" data-comment-id="${comment.id_comentario}">Responder</button>`;
        }

        commentElement.innerHTML = `
            <div class="comment-header">
                <strong>${comment.username}</strong> (${comment.created_at}):
            </div>
            <p>${comment.comment_text}</p>
            <div class="comment-actions">
                ${replyButtonHtml}
            </div>
            <div class="replies-container"></div> <!-- Contenedor para las respuestas anidadas -->
            <div class="reply-form-container" style="display:none;"></div> <!-- Contenedor para el formulario de respuesta -->
        `;

        const repliesContainer = commentElement.querySelector('.replies-container');
        if (comment.replies && comment.replies.length > 0) {
            // Ordenar las respuestas por fecha ascendente para que se vean en orden cronológico
            comment.replies.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
            comment.replies.forEach(reply => {
                repliesContainer.appendChild(createCommentElement(reply)); // Recursividad
            });
        }

        return commentElement;
    };

    const loadComments = async () => {
        try {
            const response = await fetch('comments_process.php?action=get_comments');
            const data = await response.json();

            if (data.success) {
                commentsDiv.innerHTML = '';
                if (data.comments.length === 0) {
                    commentsDiv.innerHTML = '<p>No hay comentarios aún. ¡Sé el primero en comentar!</p>';
                } else {
                    data.comments.forEach(comment => {
                        commentsDiv.appendChild(createCommentElement(comment));
                    });
                }
                attachReplyButtonListeners(); // Adjuntar listeners después de cargar todos los comentarios
            } else {
                commentsDiv.innerHTML = `<p>Error al cargar comentarios: ${data.message}</p>`;
            }
        } catch (error) {
            console.error('Error al cargar comentarios:', error);
            commentsDiv.innerHTML = '<p>No se pudieron cargar los comentarios en este momento.</p>';
        }
    };

    // Función para adjuntar listeners a los botones de responder
    const attachReplyButtonListeners = () => {
        document.querySelectorAll('.reply-btn').forEach(button => {
            // Remover listener previo para evitar duplicados si se llama varias veces
            button.removeEventListener('click', handleReplyButtonClick);
            button.addEventListener('click', handleReplyButtonClick);
        });
    };

    const handleReplyButtonClick = (event) => {
        const parentCommentId = event.target.dataset.commentId;
        const commentElement = event.target.closest('.comment');
        const replyFormContainer = commentElement.querySelector('.reply-form-container');

        // Ocultar otros formularios de respuesta abiertos para evitar múltiples formularios
        document.querySelectorAll('.reply-form-container').forEach(container => {
            if (container !== replyFormContainer) {
                container.style.display = 'none';
                container.innerHTML = ''; // Limpiar el contenido del formulario oculto
            }
        });

        // Si el formulario está oculto o vacío, lo mostramos; de lo contrario, lo ocultamos
        if (replyFormContainer.style.display === 'none' || replyFormContainer.innerHTML === '') {
            replyFormContainer.style.display = 'block';
            replyFormContainer.innerHTML = `
                <textarea class="reply-input" placeholder="Escribe tu respuesta..."></textarea>
                <button class="submit-reply-btn" data-parent-id="${parentCommentId}">Enviar Respuesta</button>
                <button class="cancel-reply-btn">Cancelar</button>
            `;
            // Adjuntar listeners a los botones del formulario recién creado
            replyFormContainer.querySelector('.submit-reply-btn').addEventListener('click', handleSubmitReply);
            replyFormContainer.querySelector('.cancel-reply-btn').addEventListener('click', () => {
                replyFormContainer.style.display = 'none';
                replyFormContainer.innerHTML = '';
            });
        } else {
            replyFormContainer.style.display = 'none';
            replyFormContainer.innerHTML = '';
        }
    };

    const handleSubmitReply = async (event) => {
        const parentCommentId = event.target.dataset.parentId;
        const replyInput = event.target.closest('.reply-form-container').querySelector('.reply-input');
        const replyText = replyInput.value.trim();

        if (replyText === '') {
            alert('La respuesta no puede estar vacía.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add_reply');
        formData.append('reply_text', replyText);
        formData.append('parent_comment_id', parentCommentId);

        try {
            const response = await fetch('comments_process.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();

            if (data.success) {
                alert(data.message);
                // Encontrar el contenedor de respuestas del comentario padre
                const parentCommentElement = document.querySelector(`.comment[data-comment-id="${parentCommentId}"]`);
                if (parentCommentElement) {
                    const repliesContainer = parentCommentElement.querySelector('.replies-container');
                    // Añadir la nueva respuesta al final de las respuestas existentes
                    repliesContainer.appendChild(createCommentElement(data.comment));
                }
                // Limpiar y ocultar el formulario de respuesta
                replyInput.value = '';
                event.target.closest('.reply-form-container').style.display = 'none';
                event.target.closest('.reply-form-container').innerHTML = '';
                attachReplyButtonListeners(); // Re-adjuntar listeners para los nuevos botones de respuesta
            } else {
                alert('Error al añadir respuesta: ' + data.message);
            }
        } catch (error) {
            console.error('Error al enviar respuesta:', error);
            alert('Ocurrió un error al enviar la respuesta.');
        }
    };

    // Listener para el comentario principal (ya existente)
    if (submitCommentBtn) {
        submitCommentBtn.addEventListener('click', async () => {
            const commentText = commentInput.value.trim();

            if (commentText === '') {
                alert('El comentario no puede estar vacío.');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'add_comment');
            formData.append('comment_text', commentText);

            try {
                const response = await fetch('comments_process.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    // Añadir el nuevo comentario al principio de la lista principal
                    const newCommentElement = createCommentElement(data.comment);
                    commentsDiv.prepend(newCommentElement);
                    commentInput.value = '';
                    attachReplyButtonListeners(); // Adjuntar listeners al nuevo comentario
                } else {
                    alert('Error al añadir comentario: ' + data.message);
                }
            } catch (error) {
                console.error('Error al enviar comentario:', error);
                alert('Ocurrió un error al enviar el comentario.');
            }
        });
    }

    const shareWhatsappBtn = document.getElementById('shareWhatsappBtn');
    if (shareWhatsappBtn) {
        shareWhatsappBtn.addEventListener('click', () => {
            const message = "¡Echa un vistazo a Ahuil! Es una plataforma increíble. Descárgala aquí: https://ahuil.wuaze.com/?i=1"; 
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        });
    }

    // Cargar comentarios al inicio
    loadComments();
});

