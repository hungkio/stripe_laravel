<x-guest-layout>
    <!-- Session Status -->
    <form method="POST" action="{{ route('checkout') }}">
        @csrf
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <div>
            <x-input-label for="card-holder-first-name" :value="__('First Name')" />
            <x-text-input id="card-holder-first-name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="first_name" />
            <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="card-holder-last-name" :value="__('Last Name')" />
            <x-text-input id="card-holder-last-name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required autofocus autocomplete="last_name" />
            <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="card-holder-email" :value="__('Email')" />
            <x-text-input id="card-holder-email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="card-holder-phone" :value="__('Phone Number')" />
            <x-text-input id="card-holder-phone" class="block mt-1 w-full" type="number" name="phone" :value="old('phone')" required autofocus autocomplete="phone" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>
        <br>

        <!-- Stripe Elements Placeholder -->
        <div id="card-element"></div>
        <br>

        <div>
            <x-input-label :value="__('example id: 4242424242424242')" />
            <x-input-label :value="__('example expire date: 12/34.')" />
        </div>
        <div class="text-center mt-2">
            <x-primary-button class="ms-3" id="card-button" data-secret="{{ $intent->client_secret }}">
                Update Payment Method
            </x-primary-button>
        </div>
    </form>

    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        const stripe = Stripe('{{ env('STRIPE_KEY') }}');

        const elements = stripe.elements();
        const cardElement = elements.create('card');

        cardElement.mount('#card-element');

        const cardHolderName = $('#card-holder-first-name').val() + ' ' + $('#card-holder-last-name').val();
        const cardButton = document.getElementById('card-button');
        const clientSecret = cardButton.dataset.secret;

        cardButton.addEventListener('click', async (e) => {
            const { setupIntent, error } = await stripe.confirmCardSetup(
                clientSecret, {
                    payment_method: {
                        card: cardElement,
                        billing_details: { name: cardHolderName.value }
                    }
                }
            );

            if (error) {
                alert(error.message)
                // Display "error.message" to the user...
            } else {
                $('form').submit()
                // alert("Cập nhật phương thức thanh toán thành công")
                // The card has been verified successfully...
            }
        });
    </script>
    </form>
</x-guest-layout>


