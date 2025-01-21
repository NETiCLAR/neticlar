// Función para actualizar el contenido en tiempo real
function updateContent(sectionId, content) {
    const previewFrame = document.getElementById('preview-frame');
    if (previewFrame && previewFrame.contentWindow) {
        const section = previewFrame.contentWindow.document.querySelector(sectionId);
        if (section) {
            section.innerHTML = content;
            saveChanges(sectionId, content);
        }
    }
}

// Función para guardar los cambios
function saveChanges(sectionId, content) {
    // Crear una copia de respaldo del index.html actual
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    const backupContent = document.documentElement.outerHTML;
    
    // Guardar el backup
    const backupBlob = new Blob([backupContent], { type: 'text/html' });
    const backupUrl = URL.createObjectURL(backupBlob);
    const backupLink = document.createElement('a');
    backupLink.href = backupUrl;
    backupLink.download = `index-backup-${timestamp}.html`;
    backupLink.click();
    
    // Actualizar el archivo original
    const updatedContent = document.documentElement.outerHTML;
    const mainBlob = new Blob([updatedContent], { type: 'text/html' });
    const mainUrl = URL.createObjectURL(mainBlob);
    const mainLink = document.createElement('a');
    mainLink.href = mainUrl;
    mainLink.download = 'index.html';
    mainLink.click();
}

// Función para cargar la vista previa inicial
function loadPreview() {
    const previewFrame = document.getElementById('preview-frame');
    if (previewFrame) {
        previewFrame.src = '../index.html';
    }
}

// Inicializar la vista previa cuando se carga la página
document.addEventListener('DOMContentLoaded', loadPreview);

// Función para sincronizar los cambios del editor
function setupEditorSync(editor, sectionId) {
    editor.addEventListener('input', debounce(() => {
        updateContent(sectionId, editor.value);
    }, 500));
}

// Función de debounce para evitar demasiadas actualizaciones
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
