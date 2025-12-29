<!-- Invoice Template Customizer Content -->
<div class="space-y-6">
    <div class="bg-info-50 border border-info-200 rounded-md p-4">
        <p class="text-sm text-info-700">
            <i class="fas fa-paint-brush mr-2"></i>
            Customize the appearance of your invoices. Changes will apply to all future invoices.
        </p>
    </div>

    <form>
        <div class="space-y-6">
            <!-- Logo Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Company Logo</label>
                <div class="flex items-center space-x-4">
                    <div class="w-32 h-32 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                        <i class="fas fa-image text-4xl text-gray-400"></i>
                    </div>
                    <div>
                        <button type="button" class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Upload Logo
                        </button>
                        <p class="text-xs text-gray-500 mt-2">PNG, JPG up to 2MB<br>Recommended: 400x400px</p>
                    </div>
                </div>
            </div>

            <!-- Color Scheme -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color</label>
                <div class="flex items-center space-x-4">
                    <input type="color" value="#3B82F6" class="h-10 w-20 rounded border border-gray-300">
                    <span class="text-sm text-gray-600">Used for headers and accents</span>
                </div>
            </div>

            <!-- Template Preview -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Template Preview</label>
                <div class="border border-gray-300 rounded-lg p-8 bg-white">
                    <div class="max-w-2xl mx-auto">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <div class="w-24 h-24 bg-gray-200 rounded mb-2"></div>
                                <p class="text-xs text-gray-500">Your Logo</p>
                            </div>
                            <div class="text-right">
                                <h2 class="text-2xl font-bold text-primary-600">INVOICE</h2>
                                <p class="text-sm text-gray-600">#INV-2025-001</p>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 pt-6 text-sm text-gray-600">
                            <p>This is a preview of how your invoice will appear...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                    <i class="fas fa-save mr-2"></i>
                    Save Template
                </button>
            </div>
        </div>
    </form>
</div>
