@extends('layouts.pos')

@section('title', 'Punto de Venta')
@section('page-title', 'Punto de Venta')

@section('content')
    <main class="flex flex-col gap-6 p-4 md:flex-row" style="height: calc(100vh - 76px);">
        {{-- Columna Izquierda: Catálogo --}}
        <div class="flex flex-col md:w-8/12">
            <div class="flex flex-col h-full p-4 bg-white rounded-lg shadow">
                <div class="flex items-center mb-4 space-x-2">
                    <button id="back-to-categories" class="hidden p-2 text-white bg-green-600 border rounded-lg hover:bg-green-500">Volver</button>
                    <input type="text" id="search-input" placeholder="Buscar..." class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div id="catalog-view" class="flex-grow pr-2 space-y-4 overflow-y-auto p-4"></div>
            </div>
        </div>

        {{-- Columna Derecha: Carrito --}}
        <div class="flex flex-col md:w-4/12">
            <div class="flex flex-col h-full p-4 bg-white rounded-lg shadow">
                <div class="pb-4 mb-4 border-b">
                    <div id="client-display" class="hidden"><div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm">Cliente:</p>
                            <p id="selected-client-name" class="text-lg font-bold text-black"></p>
                        </div>
                        <button id="remove-client-btn" class="text-xl font-bold text-red-500">&times;</button>
                    </div>
                </div>

                    <div id="client-search-area"><div class="flex items-center space-x-2">
                        <div class="relative flex-grow">
                            <input type="text" id="client-search" placeholder="Buscar cliente..." class="w-full px-4 py-2 border rounded-lg">
                            <div id="client-results" class="absolute z-10 hidden w-full mt-1 bg-white border rounded-lg shadow-lg">

                            </div>
                        </div>
                        <button id="add-client-btn" class="p-2 text-white bg-black rounded-lg">+</button>
                        </div>
                    </div>
                </div>
                <div id="cart-items" class="flex-grow py-2 pr-2 space-y-2 overflow-y-auto"><p class="text-center text-gray-700">El carrito está vacío.</p></div>
                
                {{-- CAMBIO: Se añade el área de notas --}}
               <!-- <div class="pt-2">
                    <label for="sale-notes" class="text-sm font-medium text-gray-700">Notas de la Venta</label>
                    <textarea id="sale-notes" rows="2" class="w-full p-2 mt-1 border rounded-lg" placeholder="Añadir una nota..."></textarea>
                </div>-->


                <div class="flex-shrink-0 pt-4 space-y-2 border-t">
                    <div class="flex justify-between"><span>Subtotal</span><span id="subtotal">$0.00</span></div>
                    <div class="flex justify-between"><span>IVA</span><span id="tax">$0.00</span></div>
                    <div class="flex justify-between text-xl font-bold"><span>TOTAL</span><span id="total">$0.00</span></div>
                    <div class="pt-4"><button id="checkout-btn" class="w-full py-3 font-bold text-white bg-green-600 rounded-lg">COBRAR</button></div>
                </div>
            </div>
        </div>
           {{-- Modal para Nuevo Cliente --}}
        <div id="client-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden transition-opacity bg-black bg-opacity-60">
            <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl dark:bg-gray-800">
                
                {{-- Encabezado del Modal --}}
                <div class="flex items-center justify-between pb-3 border-b dark:border-gray-700">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Crear Nuevo Cliente</h2>
                </div>

                {{-- Formulario --}}
                <form id="new-client-form" class="mt-6 space-y-4">
                    <div>
                        <label for="new-client-name" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Nombre del Cliente</label>
                        <input type="text" id="new-client-name" placeholder="Ej: Juan Pérez" class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    </div>
                    <div>
                        <label for="new-client-document" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Documento (NIT/Cédula)</label>
                        <input type="text" id="new-client-document" placeholder="Ej: 123456789" class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="flex justify-end pt-4 space-x-4">
                        <button type="button" id="cancel-client-btn" class="px-4 py-2 font-bold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">Cancelar</button>
                        <button type="submit" class="px-4 py-2 font-bold text-white rounded-lg hover:opacity-90" style="background-color: #635bff;">Guardar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
        <!--- Modal de Confirmación de Venta -->
        <div id="checkout-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-800 bg-opacity-50">
            <div class="w-1/3 p-6 bg-white rounded-lg shadow-lg">
                <h2 class="mb-4 text-2xl font-bold">Finalizar Venta</h2>
                <div class="space-y-4"><div>
                    <label class="block text-sm font-medium text-gray-700">Total a Pagar</label>
                    <input type="text" id="checkout-total" readonly class="w-full p-2 mt-1 text-2xl font-bold bg-gray-100 border rounded"></div>
                    <div>
                        <label for="received-amount" class="block text-sm font-medium text-gray-700">Dinero Recibido</label>
                        <input type="number" id="received-amount" class="w-full p-2 mt-1 border rounded">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Vuelto</label>
                        <input type="text" id="change-due" readonly class="w-full p-2 mt-1 font-bold bg-gray-100 border rounded">
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button type="button" id="cancel-checkout-btn" class="px-4 py-2 mr-2 bg-gray-300 rounded">Cancelar</button>
                    <button type="button" id="confirm-sale-btn" class="px-4 py-2 text-white bg-green-600 rounded">Confirmar Venta</button>
                </div>
            </div>
        </div>
    </main>

    
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // --- ESTADO DE LA APLICACIÓN ---
            let cart = {}; let selectedClientId = null;
            const allUnits = {!! json_encode($units) !!};
            const allCategories = {!! json_encode($categories) !!};
            const apiToken = $('meta[name="api-token"]').attr('content');
            const csrfToken = $('meta[name="csrf-token"]').attr('content');
            let currentView = 'categories';
            let selectedCategoryId = null;
           
            // --- NUEVA FUNCIÓN PARA MOSTRAR ALERTAS ---
            function showAlert(title, message, type = 'success') {
                // Renderiza el componente de Blade con los datos pasados
                const alertHtml = `
                    <div role="alert" class="p-4 transition-all duration-300 transform translate-x-full bg-white border border-gray-300 rounded-md shadow-lg alert-component">
                        <div class="flex items-start gap-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 ${type === 'success' ? 'text-green-600' : 'text-red-600'}">
                                <path stroke-linecap="round" stroke-linejoin="round" d="${type === 'success' ? 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z'}" />
                            </svg>
                            <div class="flex-1">
                                <strong class="font-medium text-gray-900">${title}</strong>
                                <p class="mt-0.5 text-sm text-gray-700">${message}</p>
                            </div>
                            <button class="dismiss-alert -m-3 rounded-full p-1.5 text-gray-500 transition-colors hover:bg-gray-50"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
                        </div>
                    </div>
                `;
                
                const alertElement = $(alertHtml);
                $('#alert-container').append(alertElement);

                // Animación de entrada
                setTimeout(() => {
                    alertElement.removeClass('translate-x-full');
                }, 10);

                // Auto-cierre después de 5 segundos
                const timeoutId = setTimeout(() => {
                    alertElement.addClass('translate-x-full');
                    setTimeout(() => alertElement.remove(), 300);
                }, 5000);

                // Cierre manual
                alertElement.find('.dismiss-alert').on('click', function() {
                    clearTimeout(timeoutId);
                    alertElement.addClass('translate-x-full');
                    setTimeout(() => alertElement.remove(), 300);
                });
            }
            
            // --- MANEJADORES DE EVENTOS ---
            $('#search-input').on('keyup', debounce(handleSearch, 300));
            $('#back-to-categories').on('click', showCategories);
            $(document).on('click', '.category-btn', function() { showProducts($(this).data('id')); });
            $(document).on('click', '.add-to-cart-btn', function() { addToCart($(this).data('product')); });
            $('#client-search').on('keyup', debounce(() => searchClients($('#client-search').val()), 300));
            $('#add-client-btn').on('click', () => $('#client-modal').removeClass('hidden'));
            $('#cancel-client-btn').on('click', () => $('#client-modal').addClass('hidden'));
            $('#new-client-form').on('submit', createClient);
            $('#remove-client-btn').on('click', removeClient);
            $(document).on('click', '.client-result-item', function() { selectClient($(this).data('id'), $(this).text()); });
            $(document).on('change', '.cart-quantity, .cart-unit, .tax-rate', function() { updateCartItem($(this).closest('.cart-item').data('id')); });
            $(document).on('click', '.remove-from-cart-btn', function() { removeFromCart($(this).closest('.cart-item').data('id')); });
            $('#checkout-btn').on('click', openCheckoutModal);
            $('#cancel-checkout-btn').on('click', () => $('#checkout-modal').addClass('hidden'));
            $('#confirm-sale-btn').on('click', saveSale);
            $('#received-amount').on('keyup', calculateChange);

            // --- FUNCIONES DE LÓGICA Y RENDERIZADO ---
             function ajaxRequest(url, method, data = {}) { return $.ajax({ url: url, method: method, headers: { 'Authorization': `Bearer ${apiToken}`, 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, data: data, }).fail(handleAjaxError); }
            
            function handleSearch() {
                const searchTerm = $('#search-input').val().toLowerCase();
                if (currentView === 'categories') {
                    renderCategories(searchTerm);
                } else {
                    loadProducts(searchTerm, selectedCategoryId);
                }
            }

            function loadProducts(searchTerm = '', categoryId = null) {
                $('#catalog-view').html('<p class="text-center text-gray-500">Cargando...</p>');
                let data = { search: searchTerm };
                if (categoryId) data.category_id = categoryId;
                ajaxRequest('/api/pos/search-products', 'GET', data).done(renderProducts);
            }

            function renderCategories(filter = '') {
                let html = '<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">';
                allCategories.forEach(cat => {
                    if (cat.name.toLowerCase().includes(filter)) {
                        html += `<div class="p-4 font-semibold text-center border border-gray-400 rounded-lg cursor-pointer hover:bg-gray-300 category-btn" data-id="${cat.id}">${cat.name} </div>`;
                    }
                });
                html += '</div>';
                $('#catalog-view').html(html || '<p>No hay categorías.</p>');
            }

            function renderProducts(products) {
                let html = '<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">';
                products.forEach(p => {
                    html += `<div class="p-2 border border-gray-400 rounded-lg cursor-pointer add-to-cart-btn" data-product='${JSON.stringify(p)}'>
                                <p class="text-sm font-semibold">${p.name}</p>
                                <p class="text-xs">$ ${parseFloat(p.price) % 1 === 0 ? parseInt(p.price) : parseFloat(p.price).toFixed(2)}</p>
                                <p class="text-xs">
                                Stock: ${parseFloat(p.stock) % 1 === 0 ? parseInt(p.stock) : parseFloat(p.stock).toFixed(2)} - ${p.unit_of_measure.name}
                                </p>
                            </div>`;
                });
                html += `</div>`;
                $('#catalog-view').html(html || '<p>No se encontraron productos.</p>');
            }
           

            function showCategories() { currentView = 'categories'; selectedCategoryId = null; $('#back-to-categories').addClass('hidden'); $('#search-input').val('').attr('placeholder', 'Buscar categoría...'); renderCategories(); }
            function showProducts(categoryId) { currentView = 'products'; selectedCategoryId = categoryId; $('#back-to-categories').removeClass('hidden'); $('#search-input').val('').attr('placeholder', 'Buscar producto...'); loadProducts('', categoryId); }
            
            function searchClients(searchTerm = '') { if(searchTerm.length < 2) { $('#client-results').addClass('hidden'); return; } ajaxRequest(`/api/pos/search-clients?search=${searchTerm}`, 'GET').done(renderClientResults); }
            //renderiza los resultados d clientes
            function renderClientResults(clients) { let html = ''; clients.forEach(c => { html += `<div class="p-2 cursor-pointer hover:bg-gray-100 client-result-item" data-id="${c.id}">${c.name}</div>`; }); $('#client-results').html(html).removeClass('hidden'); }
            function selectClient(id, name) { selectedClientId = id; $('#selected-client-name').text(name); $('#client-display').removeClass('hidden'); $('#client-search-area').addClass('hidden'); $('#client-results').addClass('hidden'); }
            function removeClient() { selectedClientId = null; $('#client-display').addClass('hidden'); $('#client-search-area').removeClass('hidden'); $('#client-search').val(''); }
            //Crear nuevo cliente
            function createClient(e) {
                e.preventDefault();
                const name = $('#new-client-name').val();
                if (!name) { showAlert('Campo Requerido', 'El nombre del cliente es obligatorio.', 'error'); return; }
                const data = { name: name, document: $('#new-client-document').val() };
                ajaxRequest('/api/pos/store-client', 'POST', data).done(response => {
                    if (response.success) {
                        showAlert('Cliente Creado', 'El nuevo cliente ha sido guardado y seleccionado.');
                        $('#client-modal').addClass('hidden');
                        $('#new-client-form')[0].reset();
                        selectClient(response.client.id, response.client.name);
                    }
                });
            }
            //Añadir producto al carrito
            function addToCart(product) { if (cart[product.id]) { cart[product.id].quantity++; } else { cart[product.id] = { product_id: product.id, name: product.name, quantity: 1, price: product.price, tax_rate: 19, unit_of_measure_id: product.unit_of_measure_id }; } renderCart(); }
            function removeFromCart(productId) { delete cart[productId]; renderCart(); }
           
            //Actualizar el item del carrito
            function updateCartItem(productId) { 
                // Capturar los elementos cuando son actualizados
                const itemDiv = $(`.cart-item[data-id="${productId}"]`);
                const quantity = parseInt(itemDiv.find('.cart-quantity').val()); 
                const unitId = parseInt(itemDiv.find('.cart-unit').val()); 
                const taxRate = parseFloat(itemDiv.find('.tax-rate').val()); 
                
                if (quantity > 0) { 
                    cart[productId].quantity = quantity; 
                    cart[productId].unit_of_measure_id = unitId; 
                    cart[productId].tax_rate = taxRate; 
                } else { 
                    delete cart[productId]; 
                } renderCart(); 
            }
            
            //Renderiza el carrito de compras 
            function renderCart() { 
                let html = ''; 
                let subtotal = 0, tax = 0; 
                
                if ($.isEmptyObject(cart)) { 
                    $('#cart-items').html('<p class="text-center text-gray-500">El carrito está vacío.</p>'); 
                }else
                {
                    for (const id in cart) {
                        const item = cart[id];
                        
                        // --- CAMBIO CLAVE: Se calcula el precio total del item considerando la unidad ---
                        const selectedUnit = allUnits.find(u => u.id == item.unit_of_measure_id);
                        const conversionFactor = selectedUnit ? parseFloat(selectedUnit.conversion_factor) : 1;            
                        const itemPrice = parseFloat(item.price) * conversionFactor;
                    
                        const itemSubtotal =  parseInt(item.quantity) * itemPrice;
                        
                        // Calcular el impuesto
                        subtotal += itemSubtotal;
                        text_tax_rate = item.tax_rate ?? 19;
                        tax += itemSubtotal * ((item.tax_rate) / 100);

                        let unitOptions = '';
                        allUnits.forEach(u => { unitOptions += `<option value="${u.id}" ${item.unit_of_measure_id == u.id ? 'selected' : ''}>${u.name}</option>`; });
                        // Dibujar el item en el carrito
                        html += `<div class="grid items-center grid-cols-12 gap-2 text-sm card cart-item" data-id="${id}">
                                    <div class="col-span-3 font-medium">${item.name}</div>
                                    <div class="col-span-2"><input type="number" value="${item.quantity}" class="w-full border-gray-300 rounded-md shadow-sm cart-quantity"></div>
                                    <div class="col-span-2"><input type="number" value="${item.tax_rate}" class="w-full border-gray-300 rounded-md shadow-sm tax-rate"></div> <p>%</p>
                                    <div class="col-span-3"><select class="w-full border-gray-300 rounded-md shadow-sm cart-unit">${unitOptions}</select></div>
                                    <div class="col-span"><button class="text-xl font-bold text-red-500 remove-from-cart-btn">&times;</button></div>
                                 </div>`;
                    }
                    $('#cart-items').html(html); 
                } 
                $('#subtotal').text(`$${subtotal.toFixed(2)}`); 
                $('#tax').text(`$${tax.toFixed(2)}`); 
                $('#total').text(`$${(subtotal + tax).toFixed(2)}`); 
            }
            

            function saveSale() {
                if (!selectedClientId) { showAlert('Acción Requerida', 'Por favor, seleccione un cliente.', 'warning'); return; }
                if ($.isEmptyObject(cart)) { showAlert('Carrito Vacío', 'No hay productos en el carrito para vender.', 'warning'); return; }

                // CAMBIO: Se obtiene el valor de las notas
                const notes = $('#sale-notes').val();

                ajaxRequest('/api/pos/store-sale', 'POST', { client_id: selectedClientId, cart: Object.values(cart), notes: notes })
                    .done(response => {
                        if (response.success) {
                            showAlert('Venta Exitosa', response.message);
                            window.open(response.receipt_url, '_blank').print();
                            resetPos();
                        }
                    });
            }
            
            function openCheckoutModal() { const total = parseFloat($('#total').text().replace('$', '')); if (total <= 0) { alert('El carrito está vacío.'); return; } $('#checkout-total').val(`$${total.toFixed(2)}`); $('#received-amount').val(total.toFixed(2)).focus().select(); calculateChange(); $('#checkout-modal').removeClass('hidden'); }
            function calculateChange() { const total = parseFloat($('#total').text().replace('$', '')); const received = parseFloat($('#received-amount').val()) || 0; $('#change-due').val(`$${(received - total).toFixed(2)}`); }

             function handleAjaxError(xhr) {
                console.error('Error en la petición AJAX:', xhr);
                const error = xhr.responseJSON;
                showAlert('Error Inesperado', error?.message || 'Ocurrió un problema de comunicación con el servidor.', 'error');
            }
            
            function debounce(func, delay) { let timeout; return function(...args) { clearTimeout(timeout); timeout = setTimeout(() => func.apply(this, args), delay); }; }
            function resetPos() { cart = {}; renderCart(); removeClient();$('#sale-notes').val(''); $('#checkout-modal').addClass('hidden'); }

            // --- INICIALIZACIÓN ---
            showCategories();
        });
    </script>
@endpush