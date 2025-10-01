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
            <div class="flex-shrink-0 py-4">
                <textarea id="sale-notes" rows="2" class="w-full p-2 border border-gray-300 rounded-md shadow-sm" placeholder="Añadir una nota..."></textarea>
            </div>
            <div class="flex-shrink-0 pt-4 space-y-2 border-t">
                <div class="flex justify-between"><span>Subtotal</span><span id="subtotal">$0.00</span></div>
                <div class="flex justify-between"><span>IVA</span><span id="tax">$0.00</span></div>
                <div class="flex justify-between text-xl font-bold"><span>TOTAL</span><span id="total">$0.00</span></div>
                <div class="pt-4"><button id="checkout-btn" class="w-full py-3 font-bold text-white bg-green-600 rounded-lg">COBRAR</button></div>
            </div>
        </div>
    </div>
        {{-- Modal para Nuevo Cliente --}}
    <div id="client-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-60 transition-opacity">
        <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl dark:bg-gray-300">
            <div class="flex items-center justify-between pb-3 border-b dark:border-gray-700">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-black">Crear Nuevo Cliente</h2>
                <button id="cancel-client-btn-icon" class="text-gray-400 hover:text-gray-800 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form id="new-client-form" class="mt-6 space-y-4">
                <div>
                    <label for="new-client-name" class="block mb-2 text-sm font-medium text-gray-800">Nombre del Cliente</label>
                    <input type="text" id="new-client-name" placeholder="Ej: Juan Pérez" class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="new-client-document" class="block mb-2 text-sm font-medium text-gray-800">Documento (NIT/Cédula)</label>
                    <input type="text" id="new-client-document" placeholder="Ej: 123456789" class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="new-client-zone" class="block mb-2 text-sm font-medium text-gray-800">Zona</label>
                    <select id="new-client-zone" class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Sin Zona</option>
                        @foreach($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="new-client-credit-limit" class="block mb-2 text-sm font-medium text-gray-800">Límite de Crédito</label>
                    <input type="number" id="new-client-credit-limit" value="0" class="w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex justify-end pt-4 space-x-4">
                    <button type="button" id="cancel-client-btn" class="px-4 py-2 font-bold text-gray-800 bg-gray-200 rounded-lg hover:bg-gray-300">Cancelar</button>
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
                {{-- CAMBIO: Se añade el área de condicion de pago --}}
                <div id="payment-condition-area" class="hidden pt-4 border-t">
                    <label class="block mb-2 text-sm font-medium">Condición de Pago</label>
                    <div class="flex gap-4">
                        <label class="flex items-center"><input type="radio" name="payment_condition" value="cash" checked class="mr-2"> Contado</label>
                        <label class="flex items-center"><input type="radio" name="payment_condition" value="credit" class="mr-2"> Crédito</label>
                    </div>
                </div>
                 <div id="payment-details-area">
                    <label for="payment-method" class="block text-sm font-medium text-gray-700">Método de Pago</label>
                    <select id="payment-method" class="w-full p-2 mt-1 border rounded">
                        @foreach($paymentMethods as $method)
                            {{-- El data-type nos ayudará a mostrar/ocultar el campo de cuenta bancaria --}}
                            <option value="{{ $method->id }}" data-type="{{ $method->type }}">{{ $method->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="bank-account-area" class="hidden">
                    <label for="bank-account" class="block text-sm font-medium text-gray-700">Cuenta de Destino</label>
                    <select id="bank-account" class="w-full p-2 mt-1 border rounded">
                        @foreach($bankAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
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
    <!-----Modal de ingreso de gastos ----------->
    <div id="expenseModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-2xl font-bold">Registrar Salida de Efectivo</h3>
                <button onclick="closeExpenseModal()" class="p-2 rounded-full hover:bg-gray-200">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="mt-4">
                <form id="expenseForm">
                    @csrf
                    <div class="mb-4">
                        <label for="expense_amount" class="block mb-2 text-sm font-medium text-gray-700">Monto</label>
                        <input type="number" id="expense_amount" name="amount" required step="any" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="expense_description" class="block mb-2 text-sm font-medium text-gray-700">Descripción</label>
                        <textarea id="expense_description" name="description" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej: Pago de domicilio, compra de agua, etc."></textarea>
                    </div>
                </form>
            </div>
            <div class="flex justify-end pt-4 mt-4 border-t">
                <button type="button" onclick="closeExpenseModal()" class="px-4 py-2 mr-3 font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Cancelar</button>
                <button type="button" onclick="submitExpense()" class="px-4 py-2 font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Guardar Egreso</button>
            </div>
        </div>
    </div>
</main> 

    
@endsection

@push('scripts')
<script>
    const expenseModal = document.getElementById('expenseModal'); //Obtener id del modal para egresos 
    function openExpenseModal() {
        expenseModal.classList.remove('hidden');
    }
    function closeExpenseModal() {
        expenseModal.classList.add('hidden');
    }
    function submitExpense() {
        const form = document.getElementById('expenseForm');
        const formData = new FormData(form);
        const url = "{{ route('pos.store.expense') }}";

        // Muestra un indicador de carga (opcional)
        const submitButton = event.target;
        submitButton.disabled = true;
        submitButton.textContent = 'Guardando...';

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': formData.get('_token'),
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: 'success',
                    title: data.message
                });
                closeExpenseModal();
                form.reset();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error inesperado.');
        })
        .finally(() => {
            // Restaura el botón
            submitButton.disabled = false;
            submitButton.textContent = 'Guardar Egreso';
        });
    }


    $(document).ready(function() {
        // --- ESTADO DE LA APLICACIÓN ---
        let cart = {}; 
        let selectedClient = null; // CAMBIO: ahora almacena el objeto completo del cliente
        const allUnits = {!! json_encode($units) !!};
        const allCategories = {!! json_encode($categories) !!};
        const apiToken = $('meta[name="api-token"]').attr('content');
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        let currentView = 'categories';
        let selectedCategoryId = null;
        tippy('[data-tippy-content]'); //Toolpit de botones
        // Cierra el modal si se presiona la tecla Escape
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !expenseModal.classList.contains('hidden')) {
                closeExpenseModal();
            }
        });


    
        // --- NUEVA FUNCIÓN PARA MOSTRAR ALERTAS ---
        function showAlert(title, message, type = 'success') {
            // Renderiza el componente de Blade con los datos pasados
            const alertHtml = `
                <div role="alert" class="p-4 transition-all duration-300 transform translate-x-full bg-white border border-gray-300 rounded-md shadow-lg alert-component">
                    <div class="flex items-start gap-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 ${type === 'success' ? 'text-green-600' : (type === 'warning' ? 'text-yellow-600' : 'text-red-600')}">
                            <path stroke-linecap="round" stroke-linejoin="round" d="${type === 'success' ? 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z' : (type === 'warning' ? 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z' : 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z')}" />
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
        //$('#search-input').on('keyup', debounce(handleSearch, 300));
        // En la sección de MANEJADORES DE EVENTOS

        $('#search-input').on('keyup', debounce(function() {
            const searchTerm = $(this).val().trim();
            
            // Solo buscar si hay al menos 1 caracter (para SKU cortos o nombres)
            if (searchTerm.length >= 1) {
                loadProducts(searchTerm, currentView === 'products' ? selectedCategoryId : null)
                    .done(function(products) {
                        if (products.length === 1 && searchTerm.length >= 3) {
                            // Auto-agregar solo si:
                            // 1. Hay exactamente 1 resultado
                            // 2. Se han escrito al menos 3 caracteres (evita agregar accidentalmente)
                            addToCart(products[0]);
                            $('#search-input').val('');
                            showAlert('Producto Agregado', `${products[0].name} añadido al carrito`, 'success');
                        } else if (products.length === 0) {
                            // Solo mostrar si escribió algo significativo
                            if (searchTerm.length >= 3) {
                                showAlert('Sin Resultados', 'No se encontró ningún producto', 'warning');
                            }
                        }
                        // Si hay múltiples resultados, se muestran en el catálogo
                    });
            } else if (searchTerm.length === 0) {
                // Si borra todo, volver a mostrar categorías
                showCategories();
            }
        }, 300)); // 300ms de delay después de dejar de escribir
        
        $('#back-to-categories').on('click', showCategories);
        $(document).on('click', '.category-btn', function() { showProducts($(this).data('id')); });
        $(document).on('click', '.add-to-cart-btn', function() { addToCart($(this).data('product')); });
        $('#client-search').on('keyup', debounce(() => searchClients($('#client-search').val()), 300));
        $('#add-client-btn').on('click', () => $('#client-modal').removeClass('hidden'));
        $('#cancel-client-btn').on('click', () => $('#client-modal').addClass('hidden'));
        $('#new-client-form').on('submit', createClient);
        $('#remove-client-btn').on('click', removeClient);
        $(document).on('click', '.client-result-item', function() { selectClient($(this).data('id'), $(this).text()); });
        $(document).on('change', '.cart-quantity, .cart-unit, .tax-rate, .cart-price', function() { 
            updateCartItem($(this).closest('.cart-item').data('id')); 
        });
        $(document).on('click', '.remove-from-cart-btn', function() { removeFromCart($(this).closest('.cart-item').data('id')); });
        $('#checkout-btn').on('click', openCheckoutModal);
        $('#cancel-checkout-btn').on('click', () => $('#checkout-modal').addClass('hidden'));
        $('#confirm-sale-btn').on('click', saveSale);
        $('#received-amount').on('keyup', calculateChange);
        
        
        // Agrega esto temporalmente para ver todos los tipos de pago disponibles
        $(document).ready(function() {
            console.log('=== PAYMENT METHODS DEBUG ===');
            $('#payment-method option').each(function() {
                console.log('Value:', $(this).val(), 'Type:', $(this).data('type'), 'Text:', $(this).text());
            });
        });
        // Actualiza tu event listener existente con más logs:
        $('#payment-method').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const selectedType = selectedOption.data('type');
            const selectedValue = selectedOption.val();
            const selectedText = selectedOption.text();
            
            console.log('=== PAYMENT METHOD CHANGED ===');
            console.log('Selected value:', selectedValue);
            console.log('Selected type:', selectedType);
            console.log('Selected text:', selectedText);
            console.log('Checking for bank_account:', selectedType === 'bank_account');
            console.log('Checking for card:', selectedType === 'card');
            
            // Si el tipo es transferencia o tarjeta, mostramos las cuentas bancarias
            if (selectedType === 'bank_account' || selectedType === 'card') {
                console.log('Showing bank account area');
                $('#bank-account-area').removeClass('hidden');
                $('#received-amount').prop('readonly', true).val(parseFloat($('#checkout-total').val().replace('$', '')));
                calculateChange();
            } else { // Si es 'cash' u 'other'
                console.log('Hiding bank account area');
                $('#bank-account-area').addClass('hidden');
                $('#received-amount').prop('readonly', false).focus().select();
            }
        });

        $('input[name="payment_condition"]').on('change', function() {
            // 'this.value' será 'cash' o 'credit'
            if (this.value === 'credit') {
                // Si es a crédito, OCULTA todo el contenedor de los detalles del pago
                $('#payment-details-area').addClass('hidden');
                $('#bank-account-area').addClass('hidden');

            } else {
                // Si es de contado, MUESTRA el contenedor
                $('#payment-details-area').removeClass('hidden');
            }
        });

        //Activar input de precio 
        $(document).on('click', '.unlock-price-btn', function() {
            // 1. Obtenemos el ID del producto desde el botón que fue presionado
            const productId = $(this).data('id');
            
            // 2. Buscamos el campo de precio correspondiente usando su ID único
            const priceInput = $(`#price-${productId}`);
            
            // 3. Habilitamos el campo, le quitamos el fondo gris, lo enfocamos y seleccionamos el texto
            priceInput.prop('disabled', false)
                    .removeClass('bg-gray-100')
                    .focus()
                    .select();
        });

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

        /*function loadProducts(searchTerm = '', categoryId = null) {
            $('#catalog-view').html('<p class="text-center text-gray-500">Cargando...</p>');
            let data = { search: searchTerm };
            if (categoryId) data.category_id = categoryId;
            ajaxRequest('/api/pos/search-products', 'GET', data).done(renderProducts);
        }*/
        function loadProducts(searchTerm = '', categoryId = null) {
            if (searchTerm && typeof searchTerm !== 'string') { 
                searchTerm = $('#search-input').val(); 
            }
            
            currentView = 'products';
            $('#catalog-view').html('<p class="text-center text-gray-500">Cargando...</p>');
            
            let data = { search: searchTerm };
            if (categoryId) data.category_id = categoryId;
            $('#back-to-categories').removeClass('hidden');
            
            // Usa tu función ajaxRequest que ya tienes definida
            return ajaxRequest('/api/pos/search-products', 'GET', data)
                .done(renderProducts);
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
            //console.log('Productos recibidos para renderizar:', products);
            let html = '<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">';
            products.forEach(p => {
                const stock = p.stock_in_location ?? 0;
                const stockMessage = `Stock: ${stock} - ${p.unit_of_measure.name}`;
                const stockColor = stock <= 0 ? 'bg-red-300' : 'bg-green-200';
                html += `<div class="flex flex-col justify-between p-2 border rounded-lg cursor-pointer add-to-cart-btn ${stockColor}" data-product='${JSON.stringify(p)}'>
                            <div>
                                <p class="text-sm font-semibold">${p.name}</p>
                                <p class="text-xs text-gray-500">SKU: ${p.sku ?? 'N/A'}</p>
                            </div>
                            <div>
                                <p class="text-xs">$${parseFloat(p.price).toFixed(2)}</p>
                                <p class="text-xs font-bold">${stockMessage}</p>
                            </div>
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
        
        // MODIFICADO: Función selectClient para obtener información completa del cliente
        function selectClient(id, name) { 
            ajaxRequest(`/api/pos/clients/${id}/credit-details`, 'GET')
                .done(response => {
                    selectedClient = {
                        id: id,
                        name: name,
                        credit_limit: response.credit_limit || 0,
                        current_debt: response.current_debt || 0
                    };
                    
                    $('#selected-client-name').text(name); 
                    $('#client-display').removeClass('hidden'); 
                    $('#client-search-area').addClass('hidden'); 
                    $('#client-results').addClass('hidden');
                    
                    // Mostrar información del límite si es necesario (opcional)
                    if (selectedClient.credit_limit > 0) {
                        console.log(`Cliente con límite de crédito: $${selectedClient.credit_limit}, Deuda actual: $${selectedClient.current_debt}`);
                    }
                })
                .fail(() => {
                    // Si falla la consulta, crear cliente básico sin límite
                    selectedClient = {
                        id: id,
                        name: name,
                        credit_limit: 0,
                        current_debt: 0
                    };
                    
                    $('#selected-client-name').text(name);
                    $('#client-display').removeClass('hidden');
                    $('#client-search-area').addClass('hidden');
                    $('#client-results').addClass('hidden');
                    
                    showAlert('Advertencia', 'No se pudo verificar el límite de crédito del cliente.', 'warning');
                });
        }
        
        // MODIFICADO: Función removeClient
        function removeClient() { 
            selectedClient = null; 
            $('#client-display').addClass('hidden'); 
            $('#client-search-area').removeClass('hidden'); 
            $('#client-search').val(''); 
        }
        
        //Crear nuevo cliente
        function createClient(e) {
            e.preventDefault();
            const name = $('#new-client-name').val();
            if (!name) { showAlert('Campo Requerido', 'El nombre del cliente es obligatorio.', 'error'); return; }
            const data = {
                name: name,
                document: $('#new-client-document').val(),
                zone_id: $('#new-client-zone').val(),
                credit_limit: $('#new-client-credit-limit').val()
            };
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
        function addToCart(product) { if (cart[product.id]) { cart[product.id].quantity++; } else { cart[product.id] = { product_id: product.id, name: product.name, quantity: 1, price: product.price, tax_rate: 0, unit_of_measure_id: product.unit_of_measure_id }; } renderCart(); }
        function removeFromCart(productId) { delete cart[productId]; renderCart(); }
    
        //Actualizar el item del carrito
        function updateCartItem(productId) { 
            // Capturar los elementos cuando son actualizados
            const itemDiv = $(`.cart-item[data-id="${productId}"]`);
            const quantity = parseInt(itemDiv.find('.cart-quantity').val());
            const price = parseFloat(itemDiv.find('.cart-price').val()); 
            const unitId = parseInt(itemDiv.find('.cart-unit').val()); 
            const taxRate = parseFloat(itemDiv.find('.tax-rate').val()); 
            
            if (quantity > 0) { 
                cart[productId].quantity = quantity;
                cart[productId].price = price; // <<< LÍNEA NUEVA PARA GUARDAR EL PRECIO >>> 
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
            }
            else
            {
                for (const id in cart) {
                    const item = cart[id];
                    
                    
                    // --- CAMBIO CLAVE: Se calcula el precio total del item considerando la unidad ---
                    const selectedUnit = allUnits.find(u => u.id == item.unit_of_measure_id);
                    const conversionFactor = selectedUnit ? parseFloat(selectedUnit.conversion_factor) : 1;            
                    const itemPrice = parseFloat(item.price) * conversionFactor;
                
                    const itemSubtotal = parseFloat(item.quantity) * parseFloat(item.price);

                    
                    // Calcular el impuesto
                    subtotal += itemSubtotal;
                    tax += itemSubtotal * ((item.tax_rate ?? 0) / 100);

                    let unitOptions = '';
                    allUnits.forEach(u => { unitOptions += `<option value="${u.id}" ${item.unit_of_measure_id == u.id ? 'selected' : ''}>${u.name}</option>`; });
                    // Dibujar el item en el carrito
                    html += `<div class="py-3 border-b border-gray-200 cart-item" data-id="${id}">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-gray-800">${item.name}</span>
                                    
                                    <div class="flex items-center space-x-3">
                                        <button 
                                            class="text-gray-400 transition-colors hover:text-green-600 unlock-price-btn" 
                                            data-id="${id}" 
                                            data-tippy-content="Cambiar precio"
                                        >
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" s
                                                    troke-linejoin="round" 
                                                    d="M12 6v12m-3-6h6M12 8.25c-2.485 0-4.5 1.5-4.5 3.375s2.015 3.375 4.5 3.375 4.5-1.5 4.5-3.375S14.485 8.25 12 8.25zM12 15.75c-2.485 0-4.5-1.5-4.5-3.375S9.515 9 12 9s4.5 1.5 4.5 3.375-2.015 3.375-4.5 3.375z" />
                                            </svg>
                                        </button>
                                        
                                        <button class="text-xl font-bold text-red-500 transition-colors hover:text-red-700 remove-from-cart-btn" data-tippy-content="Eliminar item">&times;</button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-4 gap-3 mt-2">
                                    <div>
                                        <label for="qty-${id}" class="block text-xs font-medium text-gray-500">Cantidad</label>
                                        <input type="number" id="qty-${id}" value="${item.quantity}" class="w-full p-1 mt-1 text-sm border-gray-300 rounded-md shadow-sm cart-quantity">
                                    </div>
                                    <div>
                                        <label for="price-${id}" class="block text-xs font-medium text-gray-500">Precio</label>
                                        <input type="number" id="price-${id}" value="${item.price}" class="w-full p-1 mt-1 text-sm bg-gray-50 border-gray-300 rounded-md shadow-sm cart-price" step="any" disabled>
                                    </div>
                                    <div>
                                        <label for="tax-${id}" class="block text-xs font-medium text-gray-500">IVA(%)</label>
                                        <input type="number" id="tax-${id}" value="${item.tax_rate}" class="w-full p-1 mt-1 text-sm border-gray-300 rounded-md shadow-sm tax-rate">
                                    </div>
                                    <div>
                                        <label for="unit-${id}" class="block text-xs font-medium text-gray-500">Unidad</label>
                                        <select id="unit-${id}" class="w-full p-1 mt-1 text-sm border-gray-300 rounded-md shadow-sm cart-unit">${unitOptions}</select>
                                    </div>
                                </div>
                            </div>
                            `;
                }
                $('#cart-items').html(html); 
            } 
            $('#subtotal').text(`$${subtotal.toFixed(2)}`); 
            $('#tax').text(`$${tax.toFixed(2)}`); 
            $('#total').text(`$${(subtotal + tax).toFixed(2)}`); 
            
            tippy('[data-tippy-content]');
        }

        // NUEVA FUNCIÓN: Validar límite de crédito
        function validateCreditLimit(saleTotal) {
            $('#checkout-moda').hide();
            return new Promise((resolve) => {
                if (!selectedClient || !selectedClient.credit_limit || selectedClient.credit_limit <= 0) {
                    resolve(true); // Sin límite, proceder
                    return;
                }

                const currentDebt = parseFloat(selectedClient.current_debt || 0);
                const creditLimit = parseFloat(selectedClient.credit_limit);
                const newDebt = currentDebt + saleTotal;

                if (newDebt > creditLimit) {
                    // Mostrar modal de confirmación de sobrepaso de límite
                    showCreditLimitWarning(currentDebt, creditLimit, saleTotal, newDebt, resolve);
                } else {
                    resolve(true);
                }
            });
        }

        // NUEVA FUNCIÓN: Mostrar advertencia de límite de crédito
        function showCreditLimitWarning(currentDebt, creditLimit, saleTotal, newDebt, callback) {
            $('#checkout-modal').addClass('hidden');
            const warningHtml = `
                <div id="credit-warning-modal" class="fixed inset-0 z-60 flex items-center justify-center bg-gray-800 bg-opacity-75">
                    <div class="w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl">
                        <div class="flex items-center mb-4">
                            <svg class="w-6 h-6 mr-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900">⚠️ Límite de Crédito Excedido</h3>
                        </div>
                        <div class="mb-6 space-y-2 text-sm">
                            <p><strong>Cliente:</strong> ${selectedClient.name}</p>
                            <p><strong>Límite de crédito:</strong> $${creditLimit.toFixed(2)}</p>
                            <p><strong>Deuda actual:</strong> $${currentDebt.toFixed(2)}</p>
                            <p><strong>Valor de esta venta:</strong> $${saleTotal.toFixed(2)}</p>
                            <p><strong>Nueva deuda total:</strong> <span class="font-bold text-red-600">$${newDebt.toFixed(2)}</span></p>
                            <p class="font-semibold text-red-600">Exceso: $${(newDebt - creditLimit).toFixed(2)}</p>
                        </div>
                        <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                            <p class="text-sm text-yellow-800">
                                El cliente está excediendo su límite de crédito. ¿Desea continuar con la venta de todas formas?
                            </p>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button id="cancel-credit-warning" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                                Cancelar Venta
                            </button>
                            <button id="proceed-credit-warning" class="px-4 py-2 text-white bg-red-600 rounded-md hover:bg-red-700">
                                Continuar de Todas Formas
                            </button>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(warningHtml);

            // Eventos del modal de advertencia
            $('#cancel-credit-warning').on('click', function() {
                $('#credit-warning-modal').remove();
                callback(false);
            });

            $('#proceed-credit-warning').on('click', function() {
                $('#credit-warning-modal').remove();
                callback(true);
            });
        }

        // MODIFICADO: openCheckoutModal para incluir condición de pago
        function openCheckoutModal() { 
            const total = parseFloat($('#total').text().replace('$', '')); 
            if (total <= 0) { 
                showAlert('Carrito Vacío', 'No hay productos para vender.', 'warning'); 
                return; 
            }
            
            if (!selectedClient) {
                showAlert('Cliente Requerido', 'Por favor, seleccione un cliente antes de proceder.', 'warning');
                return;
            }

            $('#checkout-total').val(`$${total.toFixed(2)}`); 
            $('#received-amount').val(total.toFixed(2)).focus().select(); 
            calculateChange(); 
            
            // Mostrar área de condición de pago si el cliente tiene límite de crédito
            if (selectedClient.credit_limit > 0) {
                $('#payment-condition-area').removeClass('hidden');
            } else {
                $('#payment-condition-area').addClass('hidden');
                // Si no tiene límite, forzar contado
                $('input[name="payment_condition"][value="cash"]').prop('checked', true);
            }
            
            $('#checkout-modal').removeClass('hidden'); 
        }
        
        function calculateChange() { 
            const total = parseFloat($('#total').text().replace('$', '')); 
            const received = parseFloat($('#received-amount').val()) || 0; 
            $('#change-due').val(`$${(received - total).toFixed(2)}`); 
        }

        // MODIFICADO: saveSale con validación de límite de crédito
        async function saveSale() {
            if (!selectedClient || $.isEmptyObject(cart)) {
                showAlert('Datos Faltantes', 'Debes seleccionar un cliente y tener productos en el carrito.', 'warning');
                return;
            }

            const paymentCondition = $('input[name="payment_condition"]:checked').val();
            const isCredit = paymentCondition === 'credit';
            const cartTotal = parseFloat($('#total').text().replace('$', ''));

            console.log('=== DEBUG SAVE SALE ===');
            console.log('Payment condition:', paymentCondition);
            console.log('Is credit:', isCredit);

            let saleData = {
                client_id: selectedClient.id,
                cart: Object.values(cart),
                notes: $('#sale-notes').val(),
                is_cash: !isCredit,
                payment_method_id: null,
                bank_account_id: null,
            };

            if (isCredit) {
                console.log('Procesando venta a crédito...');
                const canProceed = await validateCreditLimit(cartTotal);
                if (!canProceed) {
                    return;
                }
            } else { 
                console.log('Procesando venta de contado...');
                const receivedAmount = parseFloat($('#received-amount').val()) || 0;
                
                if (receivedAmount < cartTotal) {
                    showAlert('Dinero Insuficiente', 'El dinero recibido es menor al total de la venta.', 'error');
                    return;
                }

                const selectedMethod = $('#payment-method').find('option:selected');
                const methodValue = selectedMethod.val();
                const methodType = selectedMethod.data('type');
                const bankAccountValue = $('#bank-account').val();

                console.log('=== PAYMENT METHOD DEBUG ===');
                console.log('Method value:', methodValue);
                console.log('Method type:', methodType);
                console.log('Bank account value:', bankAccountValue);
                console.log('Bank account area visible:', !$('#bank-account-area').hasClass('hidden'));

                saleData.payment_method_id = methodValue;
                
                // CAMBIO: Usar los mismos tipos que en tu event listener
                if (methodType === 'bank_account' || methodType === 'card') {
                    saleData.bank_account_id = bankAccountValue || null;
                    console.log('Bank account ID assigned:', saleData.bank_account_id);
                } else {
                    console.log('Bank account ID NOT assigned - method type:', methodType);
                }
            }

            console.log('=== FINAL SALE DATA ===');
            console.log('Final sale data:', JSON.stringify(saleData, null, 2));

            ajaxRequest('/api/pos/store-sale', 'POST', saleData)
                .done(response => {
                    if (response.success) {
                        $('#checkout-modal').addClass('hidden');
                        Swal.fire({
                            title: '¡Venta Registrada!',
                            text: response.message,
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, imprimir recibo',
                            cancelButtonText: 'No, finalizar'
                        }).then((result) => {
                            if (result.isConfirmed && response.receipt_url) {
                                window.open(response.receipt_url, '_blank');
                            }
                            resetPos();
                        });
                    }
                });
        }

        function handleAjaxError(xhr) {
            console.error('Error en la petición AJAX:', xhr);
            const error = xhr.responseJSON;
            showAlert('Error Inesperado', error?.message || 'Ocurrió un problema de comunicación con el servidor.', 'error');
        }
        
        function debounce(func, delay) { let timeout; return function(...args) { clearTimeout(timeout); timeout = setTimeout(() => func.apply(this, args), delay); }; }
        
        // MODIFICADO: resetPos
        function resetPos() { 
            cart = {}; 
            renderCart(); 
            removeClient();
            $('#checkout-modal').addClass('hidden');
            $('#received-amount').val('');
            $('#change-due').val('');
            $('input[name="payment_condition"][value="cash"]').prop('checked', true);
            $('#sale-notes').val('');
        }

        // --- INICIALIZACIÓN ---
        showCategories();
    });
        
</script>
@endpush
