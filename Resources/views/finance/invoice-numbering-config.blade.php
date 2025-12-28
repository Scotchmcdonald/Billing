<x-app-layout>
    <div x-data="invoiceNumberingConfig()" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Invoice Numbering Configuration</h1>
            <p class="mt-2 text-sm text-gray-600">Customize invoice number formats to match your accounting system requirements</p>
        </div>

        <!-- Current Format Preview -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-lg shadow-lg p-6 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-primary-100 mb-2">Current Invoice Number Format</p>
                    <p class="text-3xl font-bold font-mono" x-text="previewNumber"></p>
                    <p class="text-sm text-primary-100 mt-2">Example: <span class="font-mono font-semibold" x-text="exampleNumber"></span></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-primary-100 mb-1">Next Number</p>
                    <p class="text-4xl font-bold font-mono" x-text="'#' + config.next_number"></p>
                </div>
            </div>
        </div>

        <!-- Configuration Form -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Number Format Settings</h2>
            
            <div class="space-y-6">
                <!-- Prefix -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Prefix
                        <span class="text-gray-500 font-normal">(Optional - typically 2-5 characters)</span>
                    </label>
                    <input type="text" x-model="config.prefix" @input="updatePreview()" maxlength="10" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono" placeholder="INV">
                    <p class="text-xs text-gray-500 mt-1">Common examples: INV, BILL, SI (Sales Invoice), CR (Credit)</p>
                </div>

                <!-- Separator -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Separator</label>
                    <div class="grid grid-cols-4 gap-4">
                        <template x-for="sep in separatorOptions" :key="sep.value">
                            <button @click="config.separator = sep.value; updatePreview()" :class="config.separator === sep.value ? 'bg-primary-100 border-primary-600 text-primary-700' : 'bg-white border-gray-300 text-gray-700'" class="px-4 py-2 border-2 rounded-md text-sm font-medium font-mono hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <span x-text="sep.label"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Date Components -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Include Date Components</label>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" x-model="config.include_year" @change="updatePreview()" class="rounded text-primary-600 focus:ring-primary-500 mr-3">
                            <span class="text-sm text-gray-700">Include Year (YYYY)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="config.include_month" @change="updatePreview()" class="rounded text-primary-600 focus:ring-primary-500 mr-3">
                            <span class="text-sm text-gray-700">Include Month (MM)</span>
                        </label>
                    </div>
                </div>

                <!-- Number Padding -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Number Padding (Digits)</label>
                    <select x-model.number="config.padding" @change="updatePreview()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option :value="3">3 digits (001, 002, ... 999)</option>
                        <option :value="4">4 digits (0001, 0002, ... 9999)</option>
                        <option :value="5">5 digits (00001, 00002, ... 99999)</option>
                        <option :value="6">6 digits (000001, 000002, ... 999999)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">How many digits to use for the sequential number portion</p>
                </div>

                <!-- Reset Options -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reset Sequence</label>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="radio" x-model="config.reset_period" value="never" class="mr-3 text-primary-600 focus:ring-primary-500">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Never</span>
                                <p class="text-xs text-gray-500">Numbers continue incrementing indefinitely</p>
                            </div>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" x-model="config.reset_period" value="yearly" class="mr-3 text-primary-600 focus:ring-primary-500">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Reset Yearly</span>
                                <p class="text-xs text-gray-500">Sequence resets to 1 on January 1st each year</p>
                            </div>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" x-model="config.reset_period" value="monthly" class="mr-3 text-primary-600 focus:ring-primary-500">
                            <div>
                                <span class="text-sm font-medium text-gray-900">Reset Monthly</span>
                                <p class="text-xs text-gray-500">Sequence resets to 1 on the 1st of each month</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Next Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Next Invoice Number
                        <span class="text-danger-600">*</span>
                    </label>
                    <input type="number" x-model.number="config.next_number" @input="updatePreview()" min="1" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <p class="text-xs text-gray-500 mt-1">
                        ⚠️ <strong>Warning:</strong> Changing this number could create gaps or conflicts in your numbering sequence. Only change if necessary.
                    </p>
                </div>
            </div>
        </div>

        <!-- Format Examples -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Format Examples</h2>
            <div class="space-y-3">
                <template x-for="example in formatExamples" :key="example.format">
                    <button @click="applyExample(example)" class="w-full text-left px-4 py-3 border border-gray-200 rounded-md hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900" x-text="example.name"></p>
                                <p class="text-xs text-gray-500 mt-1" x-text="example.description"></p>
                            </div>
                            <p class="text-sm font-mono font-semibold text-primary-600" x-text="example.format"></p>
                        </div>
                    </button>
                </template>
            </div>
        </div>

        <!-- Preview & Save -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Preview</h2>
            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-3">Next 5 invoice numbers will be:</p>
                <div class="space-y-2">
                    <template x-for="(num, index) in previewNumbers" :key="index">
                        <div class="px-4 py-2 bg-gray-50 rounded-md font-mono text-sm" x-text="num"></div>
                    </template>
                </div>
            </div>
            
            <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                <button @click="resetToDefaults()" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Reset to Defaults
                </button>
                <button @click="saveConfiguration()" :disabled="saving" class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:bg-gray-400">
                    <svg x-show="!saving" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="saving" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="saving ? 'Saving...' : 'Save Configuration'"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function invoiceNumberingConfig() {
            return {
                config: {
                    prefix: 'INV',
                    separator: '-',
                    include_year: true,
                    include_month: false,
                    padding: 4,
                    reset_period: 'yearly',
                    next_number: 1
                },
                separatorOptions: [
                    { label: 'Dash (-)', value: '-' },
                    { label: 'Slash (/)', value: '/' },
                    { label: 'Underscore (_)', value: '_' },
                    { label: 'None', value: '' }
                ],
                formatExamples: [
                    {
                        name: 'Standard with Year',
                        description: 'Most common format',
                        format: 'INV-2024-0001',
                        config: { prefix: 'INV', separator: '-', include_year: true, include_month: false, padding: 4 }
                    },
                    {
                        name: 'Year + Month',
                        description: 'For high-volume businesses',
                        format: 'INV-2024-12-001',
                        config: { prefix: 'INV', separator: '-', include_year: true, include_month: true, padding: 3 }
                    },
                    {
                        name: 'Simple Sequential',
                        description: 'No date components',
                        format: 'INV-00001',
                        config: { prefix: 'INV', separator: '-', include_year: false, include_month: false, padding: 5 }
                    },
                    {
                        name: 'Accounting Style',
                        description: 'QuickBooks compatible',
                        format: '2024/0001',
                        config: { prefix: '', separator: '/', include_year: true, include_month: false, padding: 4 }
                    }
                ],
                previewNumber: '',
                exampleNumber: '',
                previewNumbers: [],
                saving: false,
                
                init() {
                    this.updatePreview();
                },
                
                updatePreview() {
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const number = String(this.config.next_number).padStart(this.config.padding, '0');
                    
                    let parts = [];
                    if (this.config.prefix) parts.push(this.config.prefix);
                    if (this.config.include_year) parts.push(year);
                    if (this.config.include_month) parts.push(month);
                    parts.push(number);
                    
                    this.previewNumber = parts.join(this.config.separator);
                    this.exampleNumber = this.previewNumber;
                    
                    // Generate preview of next 5 numbers
                    this.previewNumbers = [];
                    for (let i = 0; i < 5; i++) {
                        const num = String(this.config.next_number + i).padStart(this.config.padding, '0');
                        let previewParts = [];
                        if (this.config.prefix) previewParts.push(this.config.prefix);
                        if (this.config.include_year) previewParts.push(year);
                        if (this.config.include_month) previewParts.push(month);
                        previewParts.push(num);
                        this.previewNumbers.push(previewParts.join(this.config.separator));
                    }
                },
                
                applyExample(example) {
                    Object.assign(this.config, example.config);
                    this.updatePreview();
                },
                
                resetToDefaults() {
                    this.config = {
                        prefix: 'INV',
                        separator: '-',
                        include_year: true,
                        include_month: false,
                        padding: 4,
                        reset_period: 'yearly',
                        next_number: 1
                    };
                    this.updatePreview();
                },
                
                async saveConfiguration() {
                    if (!confirm('This will change how all future invoices are numbered. Are you sure?')) {
                        return;
                    }
                    
                    this.saving = true;
                    // API call to save configuration
                    await new Promise(resolve => setTimeout(resolve, 1500));
                    
                    alert('Invoice numbering configuration saved successfully!');
                    this.saving = false;
                }
            }
        }
    </script>
</x-app-layout>
