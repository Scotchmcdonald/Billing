<!-- Detailed Reports Content -->
<div class="space-y-6">
    <!-- Report Filters -->
    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Report Filters</h3>
        <form method="GET" action="{{ route('billing.finance.reports-hub') }}" id="report-filter-form">
            <input type="hidden" name="tab" value="reports">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <select name="date_range" id="date_range_select" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" onchange="toggleCustomDateRange()">
                        <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_quarter" {{ request('date_range') == 'this_quarter' ? 'selected' : '' }}>This Quarter</option>
                        <option value="this_year" {{ request('date_range') == 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                
                <!-- Custom Date Range Inputs (Hidden by default) -->
                <div id="custom_date_inputs" class="md:col-span-2 grid grid-cols-2 gap-4 {{ request('date_range') == 'custom' ? '' : 'hidden' }}">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Report Type</label>
                    <select name="report_type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="revenue_summary" {{ request('report_type') == 'revenue_summary' ? 'selected' : '' }}>Revenue Summary</option>
                        <option value="invoice_report" {{ request('report_type') == 'invoice_report' ? 'selected' : '' }}>Invoice Report</option>
                        <option value="payment_report" {{ request('report_type') == 'payment_report' ? 'selected' : '' }}>Payment Report</option>
                        <option value="client_activity" {{ request('report_type') == 'client_activity' ? 'selected' : '' }}>Client Activity</option>
                        <option value="tax_summary" {{ request('report_type') == 'tax_summary' ? 'selected' : '' }}>Tax Summary</option>
                        <option value="retainer_report" {{ request('report_type') == 'retainer_report' ? 'selected' : '' }}>Retainer Usage</option>
                        <option value="churn_report" {{ request('report_type') == 'churn_report' ? 'selected' : '' }}>Churn & Retention</option>
                        <option value="quote_conversion" {{ request('report_type') == 'quote_conversion' ? 'selected' : '' }}>Quote Conversion</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Group By</label>
                    <select name="group_by" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="client" {{ request('group_by') == 'client' ? 'selected' : '' }}>Client</option>
                        <option value="service_type" {{ request('group_by') == 'service_type' ? 'selected' : '' }}>Service Type</option>
                        <option value="date" {{ request('group_by') == 'date' ? 'selected' : '' }}>Date</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                        <i class="fas fa-search mr-2"></i>
                        Generate Report
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function toggleCustomDateRange() {
            const select = document.getElementById('date_range_select');
            const customInputs = document.getElementById('custom_date_inputs');
            if (select.value === 'custom') {
                customInputs.classList.remove('hidden');
            } else {
                customInputs.classList.add('hidden');
            }
        }
    </script>

    <!-- Report Results -->
    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">
                @if(($reportType ?? '') == 'revenue_summary') Revenue Summary
                @elseif(($reportType ?? '') == 'client_activity') Client Activity
                @elseif(($reportType ?? '') == 'tax_summary') Tax Summary
                @elseif(($reportType ?? '') == 'retainer_report') Retainer Usage
                @elseif(($reportType ?? '') == 'churn_report') Churn & Retention
                @elseif(($reportType ?? '') == 'quote_conversion') Quote Conversion
                @else Invoices
                @endif
            </h3>
            
            <!-- Summary Metrics -->
            <div class="flex gap-4 text-sm">
                @if(($reportType ?? '') == 'revenue_summary')
                    <div class="bg-gray-50 px-3 py-1 rounded-md border border-gray-200">
                        <span class="text-gray-500">Total Revenue:</span>
                        <span class="font-semibold text-gray-900">${{ number_format($reportData->sum('revenue'), 2) }}</span>
                    </div>
                @elseif(($reportType ?? '') == 'client_activity')
                    <div class="bg-gray-50 px-3 py-1 rounded-md border border-gray-200">
                        <span class="text-gray-500">Total Outstanding:</span>
                        <span class="font-semibold text-gray-900">${{ number_format($reportData->sum('total_invoiced') - $reportData->sum('total_paid'), 2) }}</span>
                    </div>
                @elseif(($reportType ?? '') == 'tax_summary')
                    <div class="bg-gray-50 px-3 py-1 rounded-md border border-gray-200">
                        <span class="text-gray-500">Total Tax:</span>
                        <span class="font-semibold text-gray-900">${{ number_format($reportData->sum('tax_amount'), 2) }}</span>
                    </div>
                @elseif(($reportType ?? '') == 'retainer_report')
                    <div class="bg-gray-50 px-3 py-1 rounded-md border border-gray-200">
                        <span class="text-gray-500">Total Value:</span>
                        <span class="font-semibold text-gray-900">${{ number_format($reportData->sum('total_value'), 2) }}</span>
                    </div>
                @elseif(($reportType ?? '') == 'churn_report')
                    <div class="bg-gray-50 px-3 py-1 rounded-md border border-gray-200">
                        <span class="text-gray-500">Net MRR Change:</span>
                        <span class="font-semibold {{ ($reportData->sum('new_mrr') - $reportData->sum('churn_mrr')) >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                            ${{ number_format($reportData->sum('new_mrr') - $reportData->sum('churn_mrr'), 2) }}
                        </span>
                    </div>
                @elseif(($reportType ?? '') == 'quote_conversion')
                    <div class="bg-gray-50 px-3 py-1 rounded-md border border-gray-200">
                        <span class="text-gray-500">Avg Conversion:</span>
                        <span class="font-semibold text-gray-900">
                            {{ $reportData->sum('total_quotes') > 0 ? number_format(($reportData->sum('converted_count') / $reportData->sum('total_quotes')) * 100, 1) : 0 }}%
                        </span>
                    </div>
                @else
                    <div class="bg-gray-50 px-3 py-1 rounded-md border border-gray-200">
                        <span class="text-gray-500">Total:</span>
                        <span class="font-semibold text-gray-900">${{ number_format($reportData->sum('total'), 2) }}</span>
                    </div>
                @endif
            </div>
        </div>
        <div class="overflow-x-auto">
            @if(($reportType ?? '') == 'revenue_summary')
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoices Count</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData as $row)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->month }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $row->count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">${{ number_format($row->revenue, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-6 py-4 text-center text-gray-500">No data found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif(($reportType ?? '') == 'client_activity')
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoices</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Invoiced</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Paid</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData as $row)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary-600">{{ $row->company->name ?? 'Unknown' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $row->invoice_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">${{ number_format($row->total_invoiced, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-success-600">${{ number_format($row->total_paid, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">${{ number_format($row->total_invoiced - $row->total_paid, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No data found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif(($reportType ?? '') == 'tax_summary')
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Taxable Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Tax Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData as $row)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->month }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">${{ number_format($row->taxable_amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">${{ number_format($row->tax_amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">${{ number_format($row->total_amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No data found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif(($reportType ?? '') == 'retainer_report')
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hours Purchased</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hours Remaining</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData as $row)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary-600">{{ $row->company->name ?? 'Unknown' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ number_format($row->total_purchased, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm {{ $row->total_remaining < 5 ? 'text-danger-600 font-bold' : 'text-gray-900' }}">{{ number_format($row->total_remaining, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">${{ number_format($row->total_value, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">No data found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif(($reportType ?? '') == 'churn_report')
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-success-600 uppercase tracking-wider">New Subs</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-success-600 uppercase tracking-wider">New MRR</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-danger-600 uppercase tracking-wider">Churned Subs</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-danger-600 uppercase tracking-wider">Churned MRR</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net MRR Change</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData as $row)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->month }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ $row->new_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-success-600">+${{ number_format($row->new_mrr, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ $row->churn_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-danger-600">-${{ number_format($row->churn_mrr, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium {{ ($row->new_mrr - $row->churn_mrr) >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                ${{ number_format($row->new_mrr - $row->churn_mrr, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No data found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @elseif(($reportType ?? '') == 'quote_conversion')
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Quotes</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Converted</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Conversion Rate</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Converted Value</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData as $row)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->month }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ $row->total_quotes }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">{{ $row->converted_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium {{ $row->conversion_rate > 50 ? 'text-success-600' : 'text-gray-900' }}">
                                {{ number_format($row->conversion_rate, 1) }}%
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">${{ number_format($row->total_value, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-success-600">${{ number_format($row->converted_value, 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">No data found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reportData as $invoice)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary-600">
                                <a href="{{ route('billing.finance.invoices.show', $invoice->id) }}">{{ $invoice->invoice_number }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $invoice->company->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $invoice->issue_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">${{ number_format($invoice->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">No invoices found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
