<x-app-layout>
    <div x-data="invoiceTemplateCustomizer()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Invoice Template Customizer</h1>
                    <p class="mt-2 text-sm text-gray-600">Customize your invoice PDF templates to match your brand identity</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="previewTemplate()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Preview
                    </button>
                    <button @click="saveTemplate()" :disabled="saving" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:bg-gray-400">
                        <svg x-show="!saving" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <svg x-show="saving" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-text="saving ? 'Saving...' : 'Save Template'"></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Customization Panel -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Logo Upload -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Company Logo</h2>
                    <div class="space-y-4">
                        <div x-show="template.logo_url" class="border-2 border-gray-200 rounded-lg p-4 bg-gray-50">
                            <img :src="template.logo_url" alt="Company Logo" class="max-h-24 mx-auto">
                        </div>
                        <div x-show="!template.logo_url" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-600">No logo uploaded</p>
                        </div>
                        <input type="file" @change="uploadLogo($event)" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        <p class="text-xs text-gray-500">Recommended: PNG or JPG, max 2MB, 300x100px</p>
                    </div>
                </div>

                <!-- Brand Colors -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Brand Colors</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Primary Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" x-model="template.primary_color" @change="updatePreview()" class="h-10 w-20 rounded border border-gray-300 cursor-pointer">
                                <input type="text" x-model="template.primary_color" @change="updatePreview()" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Used for headers and accents</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Secondary Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" x-model="template.secondary_color" @change="updatePreview()" class="h-10 w-20 rounded border border-gray-300 cursor-pointer">
                                <input type="text" x-model="template.secondary_color" @change="updatePreview()" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Used for table headers and highlights</p>
                        </div>
                    </div>
                </div>

                <!-- Layout Options -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Layout Options</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Template Style</label>
                            <select x-model="template.style" @change="updatePreview()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="modern">Modern (Clean & Minimal)</option>
                                <option value="classic">Classic (Traditional)</option>
                                <option value="bold">Bold (High Contrast)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Logo Position</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button @click="template.logo_position = 'left'; updatePreview()" :class="template.logo_position === 'left' ? 'bg-primary-100 border-primary-600 text-primary-700' : 'bg-white border-gray-300 text-gray-700'" class="px-3 py-2 border-2 rounded-md text-sm font-medium hover:bg-gray-50">Left</button>
                                <button @click="template.logo_position = 'center'; updatePreview()" :class="template.logo_position === 'center' ? 'bg-primary-100 border-primary-600 text-primary-700' : 'bg-white border-gray-300 text-gray-700'" class="px-3 py-2 border-2 rounded-md text-sm font-medium hover:bg-gray-50">Center</button>
                            </div>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" x-model="template.show_line_items" @change="updatePreview()" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                                <span class="text-sm text-gray-700">Show Detailed Line Items</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" x-model="template.show_payment_terms" @change="updatePreview()" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                                <span class="text-sm text-gray-700">Show Payment Terms</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Footer Text -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Footer Text</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Custom Footer Message</label>
                            <textarea x-model="template.footer_text" @input="updatePreview()" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="Thank you for your business!"></textarea>
                            <p class="text-xs text-gray-500 mt-1">Appears at the bottom of every invoice</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Instructions</label>
                            <textarea x-model="template.payment_instructions" @input="updatePreview()" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm" placeholder="Wire transfer details or payment portal link"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-8 sticky top-8">
                    <div class="border-2 border-gray-200 rounded-lg p-8 bg-white" style="aspect-ratio: 8.5/11;">
                        <!-- Invoice Header -->
                        <div class="flex items-start justify-between mb-8" :class="template.logo_position === 'center' ? 'flex-col items-center text-center' : ''">
                            <div :class="template.logo_position === 'center' ? 'mb-4' : ''">
                                <div x-show="template.logo_url" class="mb-4">
                                    <img :src="template.logo_url" alt="Logo" class="h-16">
                                </div>
                                <h1 class="text-3xl font-bold" :style="'color: ' + template.primary_color">INVOICE</h1>
                            </div>
                            <div class="text-right" :class="template.logo_position === 'center' ? 'text-center' : ''">
                                <p class="text-sm text-gray-600">Invoice #</p>
                                <p class="text-lg font-bold text-gray-900">INV-2024-0001</p>
                                <p class="text-sm text-gray-600 mt-2">Date: Dec 28, 2024</p>
                                <p class="text-sm text-gray-600">Due: Jan 27, 2025</p>
                            </div>
                        </div>

                        <!-- Bill To Section -->
                        <div class="grid grid-cols-2 gap-8 mb-8">
                            <div>
                                <p class="text-sm font-semibold text-gray-600 mb-2">BILL TO</p>
                                <p class="font-semibold text-gray-900">Acme Corporation</p>
                                <p class="text-sm text-gray-600">123 Business St</p>
                                <p class="text-sm text-gray-600">Suite 100</p>
                                <p class="text-sm text-gray-600">San Francisco, CA 94105</p>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-600 mb-2">FROM</p>
                                <p class="font-semibold text-gray-900">Your Company Name</p>
                                <p class="text-sm text-gray-600">456 Provider Ave</p>
                                <p class="text-sm text-gray-600">Building B</p>
                                <p class="text-sm text-gray-600">New York, NY 10001</p>
                            </div>
                        </div>

                        <!-- Line Items -->
                        <div x-show="template.show_line_items" class="mb-8">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr :style="'background-color: ' + template.secondary_color + '20'">
                                        <th class="text-left py-2 px-3 font-semibold text-gray-700">Description</th>
                                        <th class="text-right py-2 px-3 font-semibold text-gray-700">Qty</th>
                                        <th class="text-right py-2 px-3 font-semibold text-gray-700">Rate</th>
                                        <th class="text-right py-2 px-3 font-semibold text-gray-700">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600">
                                    <tr class="border-b border-gray-200">
                                        <td class="py-2 px-3">Monthly IT Support</td>
                                        <td class="text-right py-2 px-3">1</td>
                                        <td class="text-right py-2 px-3">$2,500.00</td>
                                        <td class="text-right py-2 px-3">$2,500.00</td>
                                    </tr>
                                    <tr class="border-b border-gray-200">
                                        <td class="py-2 px-3">On-site Consultation (4 hours)</td>
                                        <td class="text-right py-2 px-3">1</td>
                                        <td class="text-right py-2 px-3">$400.00</td>
                                        <td class="text-right py-2 px-3">$400.00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Totals -->
                        <div class="flex justify-end mb-8">
                            <div class="w-64 space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Subtotal:</span>
                                    <span class="font-medium text-gray-900">$2,900.00</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Tax (8.5%):</span>
                                    <span class="font-medium text-gray-900">$246.50</span>
                                </div>
                                <div class="flex justify-between pt-2 border-t-2 border-gray-300">
                                    <span class="font-bold text-gray-900">Total:</span>
                                    <span class="font-bold text-lg" :style="'color: ' + template.primary_color">$3,146.50</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Terms -->
                        <div x-show="template.show_payment_terms" class="mb-6 p-4 bg-gray-50 rounded-md">
                            <p class="text-xs font-semibold text-gray-700 mb-1">PAYMENT TERMS</p>
                            <p class="text-xs text-gray-600">Net 30. Late payments subject to 1.5% monthly interest.</p>
                        </div>

                        <!-- Footer -->
                        <div class="border-t border-gray-200 pt-4 mt-8">
                            <p x-show="template.footer_text" class="text-sm text-gray-600 text-center mb-2" x-text="template.footer_text"></p>
                            <p x-show="template.payment_instructions" class="text-xs text-gray-500 text-center" x-text="template.payment_instructions"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function invoiceTemplateCustomizer() {
            return {
                template: {
                    logo_url: '',
                    primary_color: '#4F46E5',
                    secondary_color: '#818CF8',
                    style: 'modern',
                    logo_position: 'left',
                    show_line_items: true,
                    show_payment_terms: true,
                    footer_text: 'Thank you for your business!',
                    payment_instructions: 'Payment can be made via ACH, check, or credit card at www.yourcompany.com/pay'
                },
                saving: false,
                
                uploadLogo(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.template.logo_url = e.target.result;
                            this.updatePreview();
                        };
                        reader.readAsDataURL(file);
                    }
                },
                
                updatePreview() {
                    // Preview updates automatically via Alpine.js reactivity
                },
                
                previewTemplate() {
                    // Generate preview PDF
                    window.open('/billing/finance/invoice-templates/preview?' + new URLSearchParams(this.template), '_blank');
                },
                
                async saveTemplate() {
                    this.saving = true;
                    // API call to save template
                    await new Promise(resolve => setTimeout(resolve, 1500));
                    alert('Invoice template saved successfully!');
                    this.saving = false;
                }
            }
        }
    </script>
</x-app-layout>
