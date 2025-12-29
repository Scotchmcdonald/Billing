<!-- Invoice Numbering Configuration Content -->
<form>
    <div class="space-y-6">
        <div class="bg-info-50 border border-info-200 rounded-md p-4">
            <p class="text-sm text-info-700">
                <i class="fas fa-hashtag mr-2"></i>
                Configure how invoice numbers are generated and formatted.
            </p>
        </div>

        <!-- Number Format -->
        <div>
            <h4 class="text-md font-medium text-gray-900 mb-3">Number Format</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prefix</label>
                    <input type="text" value="INV" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Year Format</label>
                    <select class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option>None</option>
                        <option selected>YYYY (2025)</option>
                        <option>YY (25)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Padding</label>
                    <select class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option>3 digits (001)</option>
                        <option>4 digits (0001)</option>
                        <option selected>5 digits (00001)</option>
                        <option>6 digits (000001)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Separator -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Separator</label>
            <div class="flex items-center space-x-4">
                <label class="inline-flex items-center">
                    <input type="radio" name="separator" value="-" checked class="form-radio text-primary-600">
                    <span class="ml-2">Dash (-)</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="separator" value="/" class="form-radio text-primary-600">
                    <span class="ml-2">Slash (/)</span>
                </label>
                <label class="inline-flex items-center">
                    <input type="radio" name="separator" value="" class="form-radio text-primary-600">
                    <span class="ml-2">None</span>
                </label>
            </div>
        </div>

        <!-- Preview -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
            <div class="bg-gray-50 border border-gray-300 rounded-md p-4">
                <p class="text-lg font-mono font-bold text-gray-900">INV-2025-00001</p>
                <p class="text-sm text-gray-500 mt-1">Next invoice number</p>
            </div>
        </div>

        <!-- Reset Counter -->
        <div>
            <h4 class="text-md font-medium text-gray-900 mb-3">Reset Options</h4>
            <div class="space-y-2">
                <label class="inline-flex items-center">
                    <input type="checkbox" class="form-checkbox text-primary-600">
                    <span class="ml-2 text-sm text-gray-700">Reset counter annually on January 1st</span>
                </label>
                <p class="text-xs text-gray-500 ml-6">Counter will reset to 1 at the start of each year</p>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end pt-4 border-t border-gray-200">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                <i class="fas fa-save mr-2"></i>
                Save Numbering Config
            </button>
        </div>
    </div>
</form>
