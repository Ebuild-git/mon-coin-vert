<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Requests\commandes\CommandesRequest;
use Illuminate\Http\Request;
use App\Models\{commandes, produits,Coupon, contenu_commande, config, notifications, User, Transport};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use RealRashid\SweetAlert\Facades\Alert;
//use Illuminate\Support\Facade\Mail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\OrderMail;
use App\Mail\FirstOrder;
use Illuminate\Support\Facades\DB;
use App\Notifications\NewOrder;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Contracts\Mail\Mailable;
use App\Services\PayUService\Exception;
use Illuminate\Validation\ValidationException;
use Helper;

use App\Http\Traits\ListGouvernorats ;


class CommandeController extends Controller
{

  public $cart;
  use ListGouvernorats;


 /*  public function __construct()
  {
    $this->middleware('auth');
  } */



  public function commander()
  {
    $configs = config::firstOrFail();
   // $paniers_session = session('cart');

   $paniers_session = session('cart', []);

  // Vérifier que $paniers_session est bien un tableau
  if (!is_array($paniers_session)) {
      $paniers_session = [];
  }
    $paniers = [];
    $total = 0;
    if(empty($paniers_session)){
      request()->session()->flash('error','La panier est vide !');
      return back();
  }

    
  if (session()->has('coupon')) {
    $coupon = session()->get('coupon');
    $value = Coupon::where('code', $coupon)->first();
    $discuont = session('coupon')['value'];
 
}

    foreach ($paniers_session as $session) {
      $produit = produits::find($session['id_produit']);
      if ($produit) {
        $paniers[] = [
          'nom' => $produit->nom,
          'id_produit' => $produit->id,
          'photo' => $produit->photo,
          'quantite' => $session['quantite'],
          'prix' => $produit->getPrice(),
          'total' => $session['quantite'] * $produit->getPrice(),
        ];
        if (session()->has('coupon')) {
        $total += $session['quantite'] * $produit->getPrice() - session('coupon')['value'];
        }else{
        $total += $session['quantite'] * $produit->getPrice();
        }
       
     //  dd($total);
      }
    }
   
   $gouvernorats = $this->getListGouvernorat();

   $transports = Transport ::all();

    return view('front.commandes.checkout', compact('configs', 'paniers', 'total','gouvernorats','transports'));
  }


  public function confirmOrder(Request $request)
  {
    $request->validate([

      'nom' => ['nullable', 'string', 'max:255'],
      'prenom' => ['nullable', 'string', 'max:255'],
      'email' => 'required',
      'coupon'=>'nullable|numeric',
      'transport_id' =>'nullable|integer|exists:transports,frais',    
        'phone' => 'required',
    ]); 

    $connecte = Auth::user();
    $configs = config::firstOrFail();
    $total = 0;
    
    if (session()->has('coupon')) {
        $coupon = session()->get('coupon');
        $value = Coupon::where('code', $coupon)->first();
        $discuont = session('coupon')['value'];
     
    }

if($connecte){
  $order = new commandes();
  $order->user_id = $connecte->id;
      $order->nom = $request->nom;
      $order->prenom = $request->prenom;
      $order->email = $request->email;
      $order->adresse = $request->adresse;
      $order->phone = $request->phone;
      $order->note = $request->note;
      $order->transport_id=$request->input('transport');
      $order->gouvernorat = $request->input('gouvernorat');
      $order->mode = $request->input('mode');
      $order->coupon = isset(session('coupon')['value']) ? session('coupon')['value'] : null;

} else{

  $order = new commandes();
      $order->nom = $request->nom;
      $order->prenom = $request->prenom;
      $order->email = $request->email;
      $order->adresse = $request->adresse;
      $order->phone = $request->phone;
      $order->note = $request->note;
      $order->transport_id=$request->input('transport');
      $order->gouvernorat = $request->input('gouvernorat');
      $order->mode = $request->input('mode');
      $order->coupon = isset(session('coupon')['value']) ? session('coupon')['value'] : null;
}

    $order->save();

   $user = new User([    
    'nom' => $request->input('nom'),
    'prenom' => $request->input('prenom'),
    'email' => $request->input('email'),
    'password' => Hash::make($request->input('phone')),
    'phone' => $request->input('phone'),
  ]);

  $existingUsersWithEmail = User::where('email', $request['email'])->exists();

  if (!$existingUsersWithEmail) {
   
    Mail::to($user->email)->send(new FirstOrder($user));
    $user->save();
}
 
    $paniers_session = Session::get('cart') ?? [];
    $total = 0;

    foreach ($paniers_session as $session) {
      $produit = produits::find($session['id_produit']);
      if ($produit) {

        $items=   contenu_commande::create([
          'id_commande' => $order->id,
          'id_produit' => $produit->id,
          'prix_unitaire' => $produit->getPrice(),
          'quantite' => $session['quantite'],
       
        ]);


        $produit->diminuer_stock($session['quantite']);
      }
    }

    //envoyer les emails
      $this->sendOrderConfirmationMail($order);
     
    //effacer le panier
  // session()->forget('cart');
   session()->forget('coupon');

    //generate notification
    $notification = new notifications();
   $notification->url = route('details_commande', ['id' => $order->id]);
    $notification->titre = "Nouvelle commande.";
   $notification->message = "Commande passée par " . $order->nom;
    $notification->type = "commande";
    $notification->save();
   

    return redirect()->route('thank-you');
  }

 



  public function sendOrderConfirmationMail($order)
  {
   
      Mail::to($order->email)->send(new OrderMail($order));
   
  }

  public function index(Request $request)
  {

    return view('front.commandes.thankyou');
  }
}
