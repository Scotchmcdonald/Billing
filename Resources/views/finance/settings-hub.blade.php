<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Billing Configuration') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('billing::finance._partials.nav')
            
            <!-- Status Bar -->
            <div class="mb-6 flex justify-end">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800">
                    <i class="fas fa-check-circle mr-1"></i>
                    All systems operational
                </span>
            </div>

            <!-- Tabbed Interface -->
    <x-billing::tabs :active="request()->query('tab', 'general')" :tabs="[
        ['id' => 'general', 'label' => 'General Settings', 'icon' => 'cog'],
        ['id' => 'integrations', 'label' => 'Integrations', 'icon' => 'plug'],
        ['id' => 'templates', 'label' => 'Invoice Templates', 'icon' => 'file-invoice'],
        ['id' => 'numbering', 'label' => 'Numbering', 'icon' => 'hashtag'],
        ['id' => 'notifications', 'label' => 'Notifications', 'icon' => 'bell'],
    ]">
        <!-- General Settings Tab -->
        <x-billing::tab-panel id="general">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">General Billing Settings</h3>
                @include('billing::finance._partials.settings-general')
            </div>
        </x-billing::tab-panel>

        <!-- Integrations Tab -->
        <x-billing::tab-panel id="integrations">
            <div class="space-y-6">
                <!-- Stripe Integration -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <img src="https://stripe.com/img/v3/home/social.png" alt="Stripe" class="h-8 w-8 mr-3">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Stripe</h3>
                                <p class="text-sm text-gray-500">Payment processing and subscriptions</p>
                            </div>
                        </div>
                        <x-status-badge :status="setting('stripe_secret_key') ? 'connected' : 'disconnected'" 
                                        :text="setting('stripe_secret_key') ? 'Connected' : 'Not Connected'" />
                    </div>
                    @include('billing::finance._partials.settings-stripe')
                </div>

                <!-- QuickBooks Integration -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/94/QuickBooks_logo.svg/200px-QuickBooks_logo.svg.png" alt="QuickBooks" class="h-8 w-auto mr-3">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">QuickBooks Online</h3>
                                <p class="text-sm text-gray-500">Accounting and bookkeeping sync</p>
                            </div>
                        </div>
                        <x-status-badge :status="setting('qbo_access_token') ? 'connected' : 'disconnected'" 
                                        :text="setting('qbo_access_token') ? 'Connected' : 'Not Connected'" />
                    </div>
                    @include('billing::finance._partials.settings-quickbooks')
                </div>

                <!-- Xero Integration -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <img src="https://upload.wikimedia.org/wikipedia/en/9/9f/Xero_software_logo.svg" alt="Xero" class="h-8 w-auto mr-3">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Xero</h3>
                                <p class="text-sm text-gray-500">Accounting software integration</p>
                            </div>
                        </div>
                        <x-status-badge :status="setting('xero_access_token') ? 'connected' : 'disconnected'" 
                                        :text="setting('xero_access_token') ? 'Connected' : 'Not Connected'" />
                    </div>
                    @include('billing::finance._partials.settings-xero')
                </div>

                <!-- PayPal Integration -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <img src="https://www.paypalobjects.com/webstatic/mktg/logo/pp_cc_mark_111x69.jpg" alt="PayPal" class="h-8 w-auto mr-3">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">PayPal</h3>
                                <p class="text-sm text-gray-500">Alternative payment gateway</p>
                            </div>
                        </div>
                        <x-status-badge :status="setting('paypal_client_id') ? 'connected' : 'disconnected'" 
                                        :text="setting('paypal_client_id') ? 'Connected' : 'Not Connected'" />
                    </div>
                    @include('billing::finance._partials.settings-paypal')
                </div>

                <!-- GoCardless Integration -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/86/GoCardless_logo.svg/200px-GoCardless_logo.svg.png" alt="GoCardless" class="h-6 w-auto mr-3">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">GoCardless</h3>
                                <p class="text-sm text-gray-500">Direct debit payment processing</p>
                            </div>
                        </div>
                        <x-status-badge :status="setting('gocardless_access_token') ? 'connected' : 'disconnected'" 
                                        :text="setting('gocardless_access_token') ? 'Connected' : 'Not Connected'" />
                    </div>
                    @include('billing::finance._partials.settings-gocardless')
                </div>

                <!-- Slack Integration -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/d/d5/Slack_icon_2019.svg" alt="Slack" class="h-8 w-8 mr-3">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Slack</h3>
                                <p class="text-sm text-gray-500">Billing notifications and alerts</p>
                            </div>
                        </div>
                        <x-status-badge :status="setting('slack_webhook_url') ? 'connected' : 'disconnected'" 
                                        :text="setting('slack_webhook_url') ? 'Connected' : 'Not Connected'" />
                    </div>
                    @include('billing::finance._partials.settings-slack')
                </div>

                <!-- Microsoft Teams Integration -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c9/Microsoft_Office_Teams_%282018%E2%80%93present%29.svg/200px-Microsoft_Office_Teams_%282018%E2%80%93present%29.svg.png" alt="Microsoft Teams" class="h-8 w-8 mr-3">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Microsoft Teams</h3>
                                <p class="text-sm text-gray-500">Channel notifications via Webhook</p>
                            </div>
                        </div>
                        <x-status-badge :status="setting('teams_webhook_url') ? 'connected' : 'disconnected'" 
                                        :text="setting('teams_webhook_url') ? 'Connected' : 'Not Connected'" />
                    </div>
                    @include('billing::finance._partials.settings-teams')
                </div>

                <!-- Twilio SMS Integration -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/7/7e/Twilio-logo-red.svg" alt="Twilio" class="h-8 w-auto mr-3">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Twilio SMS</h3>
                                <p class="text-sm text-gray-500">Payment reminders via SMS</p>
                            </div>
                        </div>
                        <x-status-badge :status="setting('twilio_account_sid') ? 'connected' : 'disconnected'" 
                                        :text="setting('twilio_account_sid') ? 'Connected' : 'Not Connected'" />
                    </div>
                    @include('billing::finance._partials.settings-twilio')
                </div>
            </div>
        </x-billing::tab-panel>

        <!-- Invoice Templates Tab -->
        <x-billing::tab-panel id="templates" lazy="true">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Template Customization</h3>
                @include('billing::finance._partials.invoice-template-content')
            </div>
        </x-billing::tab-panel>

        <!-- Numbering Tab -->
        <x-billing::tab-panel id="numbering" lazy="true">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Numbering Configuration</h3>
                @include('billing::finance._partials.invoice-numbering-content')
            </div>
        </x-billing::tab-panel>

        <!-- Notifications Tab -->
        <x-billing::tab-panel id="notifications" lazy="true">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Email Notifications</h3>
                <p class="text-sm text-gray-600 mb-6">Configure automated email notifications for billing events</p>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-4 border-b border-gray-200">
                        <div class="flex-1">
                            <label class="font-medium text-gray-900">Invoice Created</label>
                            <p class="text-sm text-gray-500">Send notification when invoice is generated</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between py-4 border-b border-gray-200">
                        <div class="flex-1">
                            <label class="font-medium text-gray-900">Payment Received</label>
                            <p class="text-sm text-gray-500">Send confirmation when payment is processed</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                    
                    <div class="flex items-center justify-between py-4 border-b border-gray-200">
                        <div class="flex-1">
                            <label class="font-medium text-gray-900">Overdue Reminder</label>
                            <p class="text-sm text-gray-500">Send reminder for overdue invoices</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        </label>
                    </div>
                </div>
            </div>
        </x-billing::tab-panel>
    </x-billing::tabs>
        </div>
    </div>
</x-app-layout>

