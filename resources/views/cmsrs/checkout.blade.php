@extends('layouts.default')
@section('content')
  <h1 class="mb-4 mt-3">{{__('Checkout')}}</h1>

    <div class="container">
        <div class="col">
            <!--
            <div class="alert alert-secondary" role="alert">
            {{ __('SHOPPING CART') }}:
            </div>
            -->
            <ul class="list-unstyled">
              <li class="mt-2" v-for="item in cart" v-bind:key="item.id">              
              <div class="container">

                  <div class="row mt-3">
                    <div class="col-sm"> 
                      <a v-bind:href="item.url_product" ><img v-bind:src="item.url_image"  v-bind:alt="item.name" ></a>                                
                    </div>
                    <div class="col-sm"> 
                      <div><a v-bind:href="item.url_product" >@{{ item.name }}</a></div>                    
                    </div>            
                    <div class="col-sm"> 
                      <span>@{{ item.price_description }} x @{{ item.qty }}</span>
                      <div class="button-group">
                        <button class="btn" v-on:click="increment(item)">+</button>
                        <button class="btn" v-on:click="decrement(item)">-</button>
                      </div>
                    </div>
                  </div>
                </div>
              </li>
            </ul>
            <div v-if="cart.length">
                <div class="cart-total">{{ __('Products price') }}: @{{ total_sanit }} </div>
                <br/><br/>
  <!--              <button class="btn" v-on:click="tobank()">{{ __('Go to bank') }}</button>-->                                    
            </div>

        </div>


    </div> <!-- div container -->    


  <form action="/post/checkout"  method="post">
    @csrf
    <div id="basket-storage"></div>
    <input  type="hidden"  name="lang" value="{{ $lang }}" />

      @error('products')
        <div class="alert alert-danger mt-2 mb-5">
          {{ $message }}
        </div>
      @enderror

      <div class="container">
        <div class="row">   
        <div class="col-sm-8">


          <!-- email -->
          <div class="row form-group d-flex align-items-center mb-3">
            <label for="email" class="col-sm-2 col-form-label" > {{ __('Email')  }}</label>
            <div  class="col-sm-10">
              <input id="email" type="text" name="email"  style="width: 80%;" class=" @error('email') is-invalid @enderror"   @if (Auth::check()) readonly value="{{ Auth::user()->email }}" @else  value="{{ old('email') }}" @endif>
              @error('email')
                  <div class="invalid-feedback"   style="display: block;" >{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- first_name -->
          <div class="row form-group d-flex align-items-center mb-3">
            <label for="first_name" class="col-sm-2 col-form-label" > {{ __('First name')  }}</label>
            <div  class="col-sm-10">
              <input id="first_name" type="text" name="first_name" style="width: 80%;" class="@error('first_name') is-invalid @enderror" value="{{ old('first_name') }}">
              @error('first_name')
                  <div class="invalid-feedback"  style="display: block;" >{{ $message }}</div>
              @enderror
            </div>
          </div>

          <!-- last_name -->
          <div class="row form-group d-flex align-items-center mb-3">
            <label for="last_name" class="col-sm-2 col-form-label"> {{ __('Last name')  }}</label>
            <div  class="col-sm-10">

            <input id="last_name" type="text" name="last_name" style="width: 80%;" class="@error('last_name') is-invalid @enderror" value="{{ old('last_name') }}">
            @error('last_name')
                <div class="invalid-feedback"  style="display: block;" >{{ $message }}</div>
            @enderror
            </div>

          </div>

          <!-- address -->
          <div class="row form-group d-flex align-items-center mb-3">
            <label for="address" class="col-sm-2 col-form-label"> {{ __('Address')  }}</label>
            <div  class="col-sm-10">

            <input id="address" type="text" name="address"  style="width: 80%;" class="@error('address') is-invalid @enderror" value="{{ old('address') }}">
            @error('address')
                <div class="invalid-feedback"  style="display: block;" >{{ $message }}</div>
            @enderror
            </div>

          </div>

          <!-- country -->
          <div class="row form-group d-flex align-items-center mb-3">
            <label for="country" class="col-sm-2 col-form-label"> {{ __('Country')  }}</label>
            <div  class="col-sm-10">

            <input id="country" type="text" name="country" style="width: 80%;" class="@error('country') is-invalid @enderror"  value="{{ old('country') }}" >
            @error('country')
                <div class="invalid-feedback"  style="display: block;" >{{ $message }}</div>
            @enderror
            </div>

          </div>

          <!-- city -->
          <div class="row form-group d-flex align-items-center mb-3">
            <label for="city"  class="col-sm-2 col-form-label"> {{ __('City')  }}</label>
            <div  class="col-sm-10">

            <input id="city" type="text" name="city"  style="width: 80%;" class="@error('city') is-invalid @enderror"  value="{{ old('city') }}">
            @error('city')
                <div class="invalid-feedback"  style="display: block;" >{{ $message }}</div>
            @enderror
            </div>

          </div>

          <!-- telephone -->
          <div class="row form-group d-flex align-items-center mb-3">
            <label for="telephone" class="col-sm-2 col-form-label"> {{ __('Telephone')  }}</label>
            <div  class="col-sm-10">

            <input id="telephone" type="text" name="telephone" style="width: 80%;" class="@error('telephone') is-invalid @enderror"  value="{{ old('telephone') }}">
            @error('telephone')
                <div class="invalid-feedback"  style="display: block;" >{{ $message }}</div>
            @enderror
            </div>

          </div>

          <!-- postcode -->
          <div class="row form-group d-flex align-items-center mb-3">
            <label for="postcode" class="col-sm-2 col-form-label"> {{ __('Postcode')  }}</label>
            <div  class="col-sm-10">

            <input id="postcode" type="text" name="postcode" style="width: 80%;" class="@error('postcode') is-invalid @enderror" value="{{ old('postcode') }}">
            @error('postcode')
                <div class="invalid-feedback"  style="display: block;" >{{ $message }}</div>
            @enderror
            </div>

          </div>

        </div><!-- div col fields -->    

        <div class="col-sm-4">   

          <div class="mt-4"> 
            <h4>{{ __('Deliver') }}</h4>
            @foreach ($delivers as $key_deliver => $deliver)
              @php
                $deliverPrice = $deliver['price'];
              @endphp
              <div class="form-check">
                <input class="form-check-input" type="radio" name="deliver" id="{{ $key_deliver }}" value="{{ $key_deliver }}"  v-on:change="deliver( '{{ $deliverPrice }}' )"  @if(old("deliver") == $key_deliver ) checked @endif >
                <label class="form-check-label" for="{{ $key_deliver }}">
                  {{ __($deliver['name']) }} ({{   $deliver['price_description']  }})
                </label>
              </div>

            @endforeach
            <input id="deliver-old-price"  type="hidden" value=" @if(old('deliver')) {{$delivers[old('deliver')]['price']}} @else 0 @endif" >            
            @error('deliver')
                  <div class="invalid-feedback"  style="display: block;" >{{ $message }}</div>
            @enderror

          </div><!-- div deliver -->            

          <div class="mt-5">        
            <h4>{{ __('Payment') }}</h4>
            @foreach ($payments as $key_payment => $payment)
              <div class="form-check">
                <input class="form-check-input" type="radio" name="payment" id="{{ $key_payment }}" value="{{ $key_payment }}"  @if(old("payment") == $key_payment ) checked @endif  @if( ($key_payment === \app\Services\Cmsrs\Shop\PaymentService::KEY_PAYU) && ( config('cmsrs.demo') ) ) disabled @endif >
                <label class="form-check-label" for="{{ $key_payment }}">
                  {{ __($payment['name']) }}
                </label>
              </div>
            @endforeach
            @error('payment')
                  <div class="invalid-feedback"   style="display: block;" >{{ $message }}</div>
            @enderror
          </div><!-- div payment -->            

        </div><!-- div col deliver and  payment-->            

        </div><!-- div row container -->    
      </div><!-- div container -->    

      <div class="d-flex">
        <div v-if="cart.length">
          <div class="m-4 cart-total font-weight-bold" >{{ __('Total') }}: @{{ total_add_deliver_sanit }} </div>                  
        </div>
        <button class="m-3 btn" v-on:click="checkout($event)">{{ __('Go to bank') }}</button>
      <div>

  </form>
@stop
