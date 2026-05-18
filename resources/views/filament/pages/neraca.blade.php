<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Kolom Aktiva -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 border-b pb-2">AKTIVA LANCAR</h2>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">1. Kas (Uang Tunai)</span>
                    <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($kas, 0, ',', '.') }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">2. Uang di Bank</span>
                    <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($bank, 0, ',', '.') }}</span>
                </div>
                
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">3. Jumlah Piutang</span>
                    <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($piutang, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">4. Persediaan Barang (Fisik)</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ number_format($persediaan_fisik, 0, ',', '.') }} Item</span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t flex justify-between items-center">
                <span class="text-lg font-bold text-gray-800 dark:text-white">Total Nilai Aktiva Kas</span>
                <span class="text-lg font-extrabold text-success-600 dark:text-success-400">Rp {{ number_format($total_aktiva_uang, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Kolom Pasiva -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 border-b pb-2">PASIVA</h2>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600 dark:text-gray-400 font-medium">1. Total Hutang (Pinjaman & Barang)</span>
                    <span class="font-bold text-danger-600 dark:text-danger-400">Rp {{ number_format($total_hutang, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="mt-6 pt-4 border-t flex justify-between items-center">
                <span class="text-lg font-bold text-gray-800 dark:text-white">Total Pasiva</span>
                <span class="text-lg font-extrabold text-danger-600 dark:text-danger-400">Rp {{ number_format($total_hutang, 0, ',', '.') }}</span>
            </div>
        </div>

    </div>
</x-filament-panels::page>