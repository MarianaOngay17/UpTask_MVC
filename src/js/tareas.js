(function(){ // IIFE

    const nuevaTareaBtn = document.querySelector('#gregar-tarea');
    nuevaTareaBtn.addEventListener('click', mostrarFormulario);

    function mostrarFormulario(){
        const modal = document.createElement('div');
        modal.classList.add('modal');
        modal.innerHTML = `
            <form class="formulario nueva-tarea">
                <legend>Añade una nueva tarea</legend>
            
            </form>
        `;
    
    
    }





})();

