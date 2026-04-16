<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiMakan - Sistem Kantin Kampus</title>
    
    <!-- 1. Memuat Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- 2. Memuat Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- 3. LOGIKA APLIKASI (Harus di sini sebelum Alpine dimuat) -->
    <script>
        // Data Dummy (Agar UI tidak kosong melompong)
        const mockMenu = [
            { id: 1, name: 'Nasi Goreng Kampung', desc: 'Nasi goreng kampung dengan ayam, telur mata sapi, dan sayur segar', price: 15000, time: '10 Menit', category: 'Makanan Utama', stock: 20, image: 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&q=80&w=400&h=300' },
            { id: 2, name: 'Mie Ayam Special', desc: 'Mie ayam jamur lengkap dengan pangsit goreng dan rebus', price: 12000, time: '8 Menit', category: 'Makanan Utama', stock: 15, image: 'https://images.unsplash.com/photo-1552611052-33e04de081de?auto=format&fit=crop&q=80&w=400&h=300' },
            { id: 3, name: 'Ayam Geprek Njerit', desc: 'Ayam goreng renyah digeprek dengan sambal bawang level 5', price: 14000, time: '12 Menit', category: 'Makanan Utama', stock: 0, image: 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?auto=format&fit=crop&q=80&w=400&h=300' },
            { id: 4, name: 'Tahu Gejrot Cirebon', desc: 'Tahu pong goreng disiram kuah asam pedas manis segar', price: 8000, time: '5 Menit', category: 'Camilan', stock: 10, image: 'https://images.unsplash.com/photo-1627308595229-7830f5c9c66e?auto=format&fit=crop&q=80&w=400&h=300' },
            { id: 5, name: 'Es Cendol Dawet', desc: 'Minuman manis tradisional dengan santan asli dan gula aren kental', price: 7000, time: '4 Menit', category: 'Minuman', stock: 8, image: 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&q=80&w=400&h=300' }
        ];

        const mockOrders = [
            { id: 'ORD001', customer: 'Rizki M.', items: [{ name: 'Nasi Goreng Kampung', qty: 1 }, { name: 'Es Cendol Dawet', qty: 1 }], total: 22000, status: 'Sedang Disiapkan' },
            { id: 'ORD002', customer: 'Siti K.', items: [{ name: 'Ayam Geprek Njerit', qty: 2 }], total: 28000, status: 'Menunggu' },
            { id: 'ORD003', customer: 'Budi S.', items: [{ name: 'Mie Ayam Special', qty: 1 }], total: 12000, status: 'Siap Diambil' }
        ];

        // Mendaftarkan komponen Alpine
        document.addEventListener('alpine:init', () => {
            Alpine.data('simakanApp', () => ({
                role: 'student', 
                menuItems: mockMenu, // Nanti ganti jadi: @json($menus ?? []) kalau database sudah ada isinya
                categories: ['Makanan Utama', 'Camilan', 'Minuman', 'Penutup'],
                activeCategory: 'Makanan Utama',
                searchQuery: '',
                cart: [],
                isCartOpen: false,
                orders: mockOrders, // Nanti ganti jadi: @json($orders ?? [])
                toast: { show: false, message: '', type: 'success' },

                // --- COMPUTED PROPERTIES ---
                get filteredMenu() {
                    return this.menuItems.filter(item => {
                        const matchCat = item.category === this.activeCategory;
                        const query = (this.searchQuery || '').toLowerCase();
                        const matchSearch = item.name.toLowerCase().includes(query);
                        return matchCat && matchSearch;
                    });
                },
                get cartTotalItems() {
                    return this.cart.reduce((sum, item) => sum + item.qty, 0);
                },
                get cartTotalHarga() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
                },
                get activeOrdersCount() {
                    return this.orders.filter(o => o.status !== 'Selesai').length;
                },
                get preparingCount() { return this.orders.filter(o => o.status === 'Sedang Disiapkan').length; },
                get readyCount() { return this.orders.filter(o => o.status === 'Siap Diambil').length; },
                get completedCount() { return this.orders.filter(o => o.status === 'Selesai').length; },

                // --- METHODS ---
                formatRupiah(number) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
                },
                showToast(message, type = 'success') {
                    this.toast = { show: true, message, type };
                    setTimeout(() => { this.toast.show = false; }, 3000);
                },
                addToCart(item) {
                    if (item.stock === 0) return;
                    const existing = this.cart.find(i => i.id === item.id);
                    if (existing) {
                        existing.qty++;
                    } else {
                        this.cart.push({ ...item, qty: 1 });
                    }
                    this.showToast(`${item.name} ditambahkan ke keranjang!`);
                },
                updateCartQty(id, delta) {
                    const item = this.cart.find(i => i.id === id);
                    if (item) {
                        item.qty += delta;
                        if (item.qty <= 0) {
                            this.cart = this.cart.filter(i => i.id !== id);
                        }
                    }
                },
                placeOrder() {
                    if (this.cart.length === 0) return;
                    const orderIdStr = String(this.orders.length + 4).padStart(3, '0');
                    const newOrder = {
                        id: `ORD${orderIdStr}`,
                        customer: 'Anda (Mahasiswa)',
                        items: this.cart.map(i => ({ name: i.name, qty: i.qty })),
                        total: this.cartTotalHarga,
                        status: 'Menunggu'
                    };
                    
                    this.orders.unshift(newOrder); 
                    this.cart = []; 
                    this.isCartOpen = false; 
                    this.showToast('Pesanan berhasil dibuat!');
                },
                updateOrderStatus(id, newStatus) {
                    const order = this.orders.find(o => o.id === id);
                    if (order) {
                        order.status = newStatus;
                        this.showToast(`Pesanan ${id} ditandai: ${newStatus}`);
                    }
                },
                getStatusClass(status) {
                    switch (status) {
                        case 'Menunggu': return 'bg-gray-100 text-gray-700 border-gray-200';
                        case 'Sedang Disiapkan': return 'bg-orange-50 text-orange-700 border-orange-200';
                        case 'Siap Diambil': return 'bg-blue-50 text-blue-700 border-blue-200';
                        case 'Selesai': return 'bg-green-50 text-green-700 border-green-200';
                        default: return 'bg-gray-100 text-gray-700 border-gray-200';
                    }
                }
            }));
        });
    </script>

    <!-- 4. Memuat Alpine.js (Di-load terakhir agar script logika di atas terbaca duluan) -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

    <!-- x-data memanggil komponen Alpine yang sudah didaftarkan di head -->
    <div x-data="simakanApp()" class="min-h-screen pb-20">
        
        <!-- HEADER -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="ph-fill ph-chef-hat text-orange-600 text-3xl"></i>
                    <div>
                        <h1 class="text-xl font-bold leading-tight text-gray-900">Makan</h1>
                        <p class="text-[11px] font-medium text-gray-500 uppercase tracking-wider">Sistem Kantin</p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Tombol Keranjang -->
                    <template x-if="role === 'student'">
                        <div class="relative">
                            <button @click="isCartOpen = true" class="p-2 text-gray-600 hover:bg-gray-100 rounded-full relative transition-colors">
                                <i class="ph ph-shopping-cart text-2xl"></i>
                                <template x-if="cartTotalItems > 0">
                                    <span class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-white shadow-sm" x-text="cartTotalItems"></span>
                                </template>
                            </button>
                        </div>
                    </template>

                    <!-- Pemilih Peran -->
                    <div class="flex bg-gray-100 p-1 rounded-lg border border-gray-200">
                        <button @click="role = 'student'" :class="role === 'student' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium transition-all">
                            <i class="ph ph-user text-lg"></i> <span class="hidden sm:inline">Mahasiswa</span>
                        </button>
                        <button @click="role = 'staff'" :class="role === 'staff' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium transition-all">
                            <i class="ph ph-users text-lg"></i> <span class="hidden sm:inline">Staf</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- TOAST NOTIFIKASI -->
        <div x-show="toast.show" x-transition.opacity.duration.500ms class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50">
            <div :class="toast.type === 'success' ? 'bg-green-50 text-green-800 border-green-200' : 'bg-gray-800 text-white border-gray-700'" class="flex items-center gap-2 px-4 py-3 rounded-lg shadow-lg text-sm font-medium border">
                <i class="ph-fill ph-check-circle text-green-500 text-xl" x-show="toast.type === 'success'"></i>
                <span x-text="toast.message"></span>
            </div>
        </div>

        <!-- MAIN KONTEN -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            
            <!-- TAMPILAN MAHASISWA -->
            <div x-show="role === 'student'" class="space-y-6">
                <!-- Pencarian -->
                <div class="relative max-w-xl">
                    <i class="ph ph-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                    <input type="text" x-model="searchQuery" placeholder="Cari menu makanan..." class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent shadow-sm">
                </div>

                <!-- Kategori -->
                <div class="flex gap-2 overflow-x-auto pb-2 pt-2 scrollbar-hide">
                    <template x-for="cat in categories" :key="cat">
                        <button @click="activeCategory = cat" 
                                :class="activeCategory === cat ? 'bg-gray-900 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-gray-900 hover:text-gray-900'"
                                class="whitespace-nowrap px-5 py-2 rounded-full text-sm font-medium transition-all" x-text="cat">
                        </button>
                    </template>
                </div>

                <!-- Grid Menu -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <template x-for="item in filteredMenu" :key="item.id">
                        <div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 hover:shadow-xl transition-all duration-300 group flex flex-col">
                            <div class="relative h-48 overflow-hidden bg-gray-100">
                                <img :src="item.image" :alt="item.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                
                                <template x-if="item.stock === 0">
                                    <div class="absolute top-3 right-3 bg-red-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-md">Habis</div>
                                </template>
                                <template x-if="item.stock > 0 && item.stock <= 10">
                                    <div class="absolute top-3 right-3 bg-gray-900/80 backdrop-blur-sm text-white text-xs font-bold px-3 py-1.5 rounded-lg shadow-md" x-text="'Sisa ' + item.stock"></div>
                                </template>
                            </div>
                            
                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="text-lg font-bold text-gray-900 line-clamp-1" x-text="item.name"></h3>
                                <p class="text-sm text-gray-500 mt-1.5 line-clamp-2 flex-grow" x-text="item.desc"></p>
                                
                                <div class="flex items-center gap-2 mt-3 text-xs font-medium text-gray-500 bg-gray-50 w-fit px-2 py-1 rounded-md">
                                    <i class="ph ph-clock text-orange-500 text-sm"></i>
                                    <span x-text="item.time"></span>
                                </div>
                                
                                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                    <span class="text-lg font-extrabold text-gray-900" x-text="formatRupiah(item.price)"></span>
                                    <button @click="addToCart(item)" :disabled="item.stock === 0" 
                                            :class="item.stock === 0 ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-orange-50 text-orange-600 hover:bg-orange-500 hover:text-white'"
                                            class="p-2.5 rounded-xl flex items-center justify-center transition-colors">
                                        <i class="ph ph-plus font-bold text-xl"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- TAMPILAN STAF -->
            <div x-show="role === 'staff'" class="space-y-6">
                <!-- Statistik -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
                        <div class="bg-orange-100 p-3.5 rounded-xl text-orange-600"><i class="ph-fill ph-cooking-pot text-2xl"></i></div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Disiapkan</p>
                            <p class="text-2xl font-extrabold text-gray-900" x-text="preparingCount"></p>
                        </div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
                        <div class="bg-blue-100 p-3.5 rounded-xl text-blue-600"><i class="ph-fill ph-package text-2xl"></i></div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Siap Diambil</p>
                            <p class="text-2xl font-extrabold text-gray-900" x-text="readyCount"></p>
                        </div>
                    </div>
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex items-center gap-4">
                        <div class="bg-green-100 p-3.5 rounded-xl text-green-600"><i class="ph-fill ph-check-circle text-2xl"></i></div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Selesai</p>
                            <p class="text-2xl font-extrabold text-gray-900" x-text="completedCount"></p>
                        </div>
                    </div>
                </div>

                <!-- Daftar Pesanan -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="divide-y divide-gray-100">
                        <template x-for="order in orders" :key="order.id">
                            <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:bg-gray-50 transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="font-extrabold text-gray-900 bg-gray-100 px-2.5 py-1 rounded-md" x-text="order.id"></span>
                                        <span class="text-sm font-medium text-gray-600 flex items-center gap-1.5">
                                            <i class="ph-fill ph-user text-gray-400"></i> <span x-text="order.customer"></span>
                                        </span>
                                        <span :class="getStatusClass(order.status)" class="px-3 py-1 rounded-full text-xs font-bold border" x-text="order.status"></span>
                                    </div>
                                    <ul class="text-sm text-gray-700 space-y-2 mt-2 p-3 bg-gray-50 rounded-xl border border-gray-100">
                                        <template x-for="(item, index) in order.items" :key="index">
                                            <li class="flex items-start gap-2">
                                                <span class="font-bold text-gray-900 w-6" x-text="item.qty + 'x'"></span> 
                                                <span x-text="item.name" class="font-medium"></span>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                                <div class="flex flex-col md:items-end gap-3 min-w-[200px]">
                                    <div class="text-right mb-2">
                                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total</p>
                                        <p class="font-extrabold text-xl text-gray-900" x-text="formatRupiah(order.total)"></p>
                                    </div>
                                    <template x-if="order.status === 'Menunggu'">
                                        <button @click="updateOrderStatus(order.id, 'Sedang Disiapkan')" class="w-full md:w-auto px-5 py-2.5 bg-orange-500 text-white text-sm font-bold rounded-xl hover:bg-orange-600">Siapkan Pesanan</button>
                                    </template>
                                    <template x-if="order.status === 'Sedang Disiapkan'">
                                        <button @click="updateOrderStatus(order.id, 'Siap Diambil')" class="w-full md:w-auto px-5 py-2.5 bg-blue-600 text-white text-sm font-bold rounded-xl hover:bg-blue-700">Tandai Siap</button>
                                    </template>
                                    <template x-if="order.status === 'Siap Diambil'">
                                        <button @click="updateOrderStatus(order.id, 'Selesai')" class="w-full md:w-auto px-5 py-2.5 bg-green-600 text-white text-sm font-bold rounded-xl hover:bg-green-700">Selesaikan</button>
                                    </template>
                                    <template x-if="order.status === 'Selesai'">
                                        <span class="px-5 py-2.5 bg-gray-100 text-gray-500 text-sm font-bold rounded-xl flex items-center justify-center gap-2"><i class="ph-fill ph-check-circle"></i> Selesai</span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </main>

        <!-- SIDEBAR KERANJANG -->
        <div x-show="isCartOpen" class="fixed inset-0 z-50 overflow-hidden" style="display: none;">
            <div x-show="isCartOpen" x-transition.opacity @click="isCartOpen = false" class="absolute inset-0 bg-black bg-opacity-60 backdrop-blur-sm"></div>
            <div x-show="isCartOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl flex flex-col">
                <div class="flex items-center justify-between p-5 border-b border-gray-100">
                    <h2 class="text-xl font-extrabold text-gray-900 flex items-center gap-2"><i class="ph-fill ph-shopping-bag"></i> Keranjang</h2>
                    <button @click="isCartOpen = false" class="p-2 hover:bg-gray-100 text-gray-500 rounded-full"><i class="ph ph-x text-xl font-bold"></i></button>
                </div>
                <div class="flex-1 overflow-y-auto p-5 space-y-4">
                    <template x-if="cart.length === 0">
                        <div class="text-center text-gray-400 mt-20 flex flex-col items-center justify-center">
                            <i class="ph ph-shopping-cart text-6xl mb-4"></i>
                            <p class="font-medium">Keranjang kamu masih kosong.</p>
                        </div>
                    </template>
                    <template x-for="item in cart" :key="item.id">
                        <div class="flex gap-4 bg-white p-3 rounded-2xl border border-gray-100 shadow-sm">
                            <img :src="item.image" :alt="item.name" class="w-20 h-20 object-cover rounded-xl">
                            <div class="flex-1 flex flex-col justify-between py-1">
                                <div>
                                    <h4 class="font-bold text-sm text-gray-900 line-clamp-1" x-text="item.name"></h4>
                                    <p class="text-orange-600 font-extrabold text-sm mt-1" x-text="formatRupiah(item.price)"></p>
                                </div>
                                <div class="flex items-center justify-between mt-2">
                                    <div class="flex items-center gap-1 bg-gray-50 border border-gray-200 rounded-lg p-1">
                                        <button @click="updateCartQty(item.id, -1)" class="w-7 h-7 flex items-center justify-center bg-white rounded shadow-sm text-gray-700"><i class="ph ph-minus font-bold"></i></button>
                                        <span class="text-sm font-bold w-6 text-center" x-text="item.qty"></span>
                                        <button @click="updateCartQty(item.id, 1)" class="w-7 h-7 flex items-center justify-center bg-white rounded shadow-sm text-gray-700"><i class="ph ph-plus font-bold"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <template x-if="cart.length > 0">
                    <div class="p-6 border-t border-gray-100 bg-white shadow-[0_-10px_40px_rgba(0,0,0,0.05)]">
                        <div class="flex justify-between items-end mb-5">
                            <span class="font-medium text-gray-500">Total</span>
                            <span class="font-extrabold text-2xl text-gray-900" x-text="formatRupiah(cartTotalHarga)"></span>
                        </div>
                        <button @click="placeOrder" class="w-full bg-orange-500 text-white font-bold py-3.5 rounded-xl hover:bg-orange-600 shadow-md">Buat Pesanan</button>
                    </div>
                </template>
            </div>
        </div>

    </div>
</body>
</html>
