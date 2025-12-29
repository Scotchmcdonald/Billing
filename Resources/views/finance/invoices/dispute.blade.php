<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dispute Invoice') }} #{{ $invoice->invoice_number }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="disputeForm()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Invoice Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Invoice Details</h3>
                            <p class="text-sm text-gray-500">{{ $invoice->company->name }}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-gray-900">${{ number_format($invoice->total, 2) }}</div>
                            <div class="text-sm text-gray-500">Due {{ $invoice->due_date->format('M d, Y') }}</div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Invoice Date:</span>
                                <span class="font-semibold text-gray-900 ml-2">{{ $invoice->invoice_date->format('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Period:</span>
                                <span class="font-semibold text-gray-900 ml-2">
                                    {{ $invoice->period_start->format('M d') }} - {{ $invoice->period_end->format('M d, Y') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-gray-500">Status:</span>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 ml-2">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dispute Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form @submit.prevent="submitDispute" class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Dispute Information</h3>

                    <!-- Dispute Reason -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Dispute Reason <span class="text-red-500">*</span>
                        </label>
                        <select 
                            x-model="formData.reason"
                            required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Select a reason</option>
                            <option value="incorrect_hours">Incorrect Hours/Time Entries</option>
                            <option value="wrong_rate">Wrong Hourly Rate Applied</option>
                            <option value="duplicate_charge">Duplicate Charge</option>
                            <option value="service_not_received">Service Not Received</option>
                            <option value="quality_issue">Service Quality Issue</option>
                            <option value="billing_error">General Billing Error</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <!-- Disputed Amount -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Disputed Amount <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center">
                            <span class="text-gray-500 mr-2">$</span>
                            <input 
                                type="number" 
                                step="0.01"
                                min="0.01"
                                :max="{{ $invoice->total }}"
                                x-model="formData.disputed_amount"
                                required
                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                placeholder="0.00">
                            <button 
                                type="button"
                                @click="formData.disputed_amount = {{ $invoice->total }}"
                                class="ml-2 px-3 py-2 text-sm bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Full Amount
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Maximum: ${{ number_format($invoice->total, 2) }}
                        </p>
                    </div>

                    <!-- Specific Line Items (optional) -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Specific Line Items (Optional)
                        </label>
                        <div class="border border-gray-200 rounded-lg divide-y divide-gray-200 max-h-64 overflow-y-auto">
                            @foreach($invoice->lineItems as $item)
                                <label class="flex items-center p-3 hover:bg-gray-50 cursor-pointer">
                                    <input 
                                        type="checkbox" 
                                        x-model="formData.line_items"
                                        value="{{ $item->id }}"
                                        class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <div class="ml-3 flex-1">
                                        <div class="text-sm font-medium text-gray-900">{{ $item->description }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $item->quantity }} × ${{ number_format($item->unit_price, 2) }}
                                        </div>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900">
                                        ${{ number_format($item->total, 2) }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Detailed Explanation -->
                    <div class="mb-6" x-data="{ charCount: 0 }">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Detailed Explanation <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            x-model="formData.explanation"
                            @input="charCount = $el.value.length"
                            required
                            rows="5"
                            minlength="20"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500 transition-all"
                            placeholder="Please provide a detailed explanation of why you're disputing this invoice..."></textarea>
                        <div class="flex items-center justify-between mt-1">
                            <p class="text-xs text-gray-500">
                                Be as specific as possible to help us resolve this quickly
                            </p>
                            <span class="text-xs" :class="charCount < 20 ? 'text-red-500' : 'text-gray-500'">
                                <span x-text="charCount"></span> / 20 minimum
                            </span>
                        </div>
                    </div>

                    <!-- Supporting Documents -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Supporting Documents (Optional)
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500">
                                        <span>Upload files</span>
                                        <input type="file" multiple class="sr-only" @change="handleFiles">
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, PDF up to 10MB each</p>
                            </div>
                        </div>
                        <div x-show="formData.files.length > 0" class="mt-2">
                            <template x-for="(file, index) in formData.files" :key="index">
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded mb-1">
                                    <span class="text-sm text-gray-700" x-text="file.name"></span>
                                    <button type="button" @click="removeFile(index)" class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Action Preferences -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pause Automated Collection During Review?
                        </label>
                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                x-model="formData.pause_dunning"
                                checked
                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-gray-700">
                                Yes, pause collection emails and late fees while this dispute is under review
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-6">
                            Recommended: This prevents additional charges while we investigate
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="{{ route('billing.finance.invoices.show', $invoice) }}" 
                           class="text-gray-600 hover:text-gray-800">
                            Cancel
                        </a>
                        <button 
                            type="submit"
                            :disabled="submitting"
                            class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <span x-show="!submitting">
                                <i class="fas fa-flag mr-2"></i>
                                Submit Dispute
                            </span>
                            <span x-show="submitting">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Submitting...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function disputeForm() {
            return {
                submitting: false,
                formData: {
                    reason: '',
                    disputed_amount: '',
                    line_items: [],
                    explanation: '',
                    files: [],
                    pause_dunning: true
                },

                handleFiles(event) {
                    const files = Array.from(event.target.files);
                    this.formData.files = [...this.formData.files, ...files];
                },

                removeFile(index) {
                    this.formData.files.splice(index, 1);
                },

                async submitDispute() {
                    this.submitting = true;

                    try {
                        const formDataToSend = new FormData();
                        
                        // Add text fields
                        Object.keys(this.formData).forEach(key => {
                            if (key !== 'files' && key !== 'line_items') {
                                formDataToSend.append(key, this.formData[key]);
                            }
                        });

                        // Add line items as JSON
                        formDataToSend.append('line_items', JSON.stringify(this.formData.line_items));

                        // Add files
                        this.formData.files.forEach((file, index) => {
                            formDataToSend.append(`files[${index}]`, file);
                        });

                        const response = await fetch('{{ route("billing.finance.invoices.dispute", $invoice) }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formDataToSend
                        });

                        if (response.ok) {
                            window.location.href = '{{ route("billing.finance.invoices.show", $invoice) }}';
                        } else {
                            alert('Error submitting dispute. Please try again.');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    } finally {
                        this.submitting = false;
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
