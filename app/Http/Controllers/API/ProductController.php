<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\cache;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Checkout;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $var_cache = Redis::get('produk');

        if(isset($var_cache)) {
            $produk = json_decode($var_cache, FALSE);

            return response()->json([
                'success' => true,
                'message' => 'List Produk Dari Redis Berhasil Ditampilkan',
                'data' => $produk,
            ]);
        }else {

            $produk = Cache::remember('produk', 10, function() {
                return Product::all();
            });

            return response()->json([
                'success' => true,
                'message' => 'List Produk Dari Database Berhasil Ditampilkan',
                'data' => $produk,
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_produk'  => 'required|unique:products',
            'harga_produk' => 'required',
            'qty_stok' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Kolom Wajib Diisi!',
                'data' => $validator->errors()
            ],401);
        } else {

            $array[] = array(

                'nama_produk' => $request->input('nama_produk'),
                'harga_produk' => $request->input('nama_produk'),
                'qty_stok' => $request->input('qty_stok'),
                'insert_user' => \Auth::User()->email,
                'update_user' => \Auth::User()->email,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            );

            $out = array_values($array);

            Redis::set('produk', json_encode($out)
            );

            $out = Product::create([
                'nama_produk' => $request->input('nama_produk'),
                'harga_produk' => $request->input('harga_produk'),
                'qty_stok' => $request->input('qty_stok')
            ]);

            if ($out) {
                return response()->json([
                    'success' => true,
                    'message' => 'List Produk Berhasil Disimpan!',
                    'data' => $out
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'List Produk Gagal Disimpan!',
                ], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $var_cache = Redis::get('produk' . $id);

        if(isset($var_cache)) {
            $var = json_decode($var_cache, FALSE);

            return response()->json([
                'success' => true,
                'message' => 'Ambil Data Dari Redis',
                'data' => $var,
            ]);
        }else {

            $var = Product::find($id);

            Redis::set('produk' . $id, $var);

            return response()->json([
                'success' => true,
                'message' => 'Ambil data dari database',
                'data' => $var,
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_produk'  => 'required',
            'harga_produk' => 'required',
            'qty_stok' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Kolom Wajib Diisi!',
                'data' => $validator->errors()
            ],401);
        } else {

            $array[] = array(
                'nama_produk' => $request->input('nama_produk'),
                'harga_produk' => $request->input('harga_produk'),
                'qty_stok' => $request->input('qty_stok'),
                'update_user' => \Auth::User()->email,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            );

            $out = array_values($array);

            Redis::set('produk' . $id, json_encode($out)
            );

            $var = Product::findOrFail($id);

            $var->update([
                'nama_produk' => $request->input('nama_produk'),
                'harga_produk' => $request->input('harga_produk'),
                'qty_stok' => $request->input('qty_stok'),
                'update_user' => \Auth::User()->email,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($var) {
                return response()->json([
                    'success' => true,
                    'message' => 'List Produk Berhasil Diupdate!',
                    'data' => $var
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'List Produk Gagal Diupdate!',
                ], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        Redis::del('produk' . $id);

        return response()->json([
            'status_code' => 201,
            'message' => 'Produk deleted'
        ]);
    }

    public function addtochart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'no_pesanan'  => 'required',
            'id_produk' => 'required',
            'qty' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Kolom Wajib Diisi!',
                'data' => $validator->errors()
            ],401);
        } else {

            $getharga=Product::select('harga_produk')->where('id', $request->input('id_produk'))->first();

            $harga_produk=$getharga->harga_produk;

            $total_harga=$request->input('qty')*$harga_produk;

            $array[] = array(

                'no_pesanan' => $request->input('no_pesanan'),
                'id_user' => \Auth::User()->id,
                'id_produk' => $request->input('id_produk'),
                'qty' => $request->input('qty'),
                'total_harga' => $total_harga,
                'tanggal_pemesanan' => $request->input('tanggal_pemesanan'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            );

            $out = array_values($array);

            Redis::set('addchartproduk'.$request->input('no_pesanan'), json_encode($out)
            );

            if ($out) {
                return response()->json([
                    'success' => true,
                    'message' => 'List Produk Berhasil Ditambah!',
                    'data' => $out
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'List Produk Gagal Ditambah!',
                ], 400);
            }
        }
    }

    public function checkout(Request $request, $id)
    {
        $var_cache = Redis::get('addchartproduk' . $id);

        if(isset($var_cache)) {
            $var = json_decode($var_cache, FALSE);


            foreach($var as $val) {

                $getstok=Product::select('qty_stok')->where('id', $val->id_produk)->first();

                $stok_tersedia=$getstok->qty_stok;

                $sisa_stok=$stok_tersedia-$val->qty;

                if($sisa_stok<=0){
                    return response()->json([
                        'success' => true,
                        'message' => 'Pembayaran Tidak bisa dilanjutkan, karena stok kehabisan',
                        'data' => $var,
                    ]);
                }else{

                    $var = Checkout::create([
                        'no_pesanan' => $val->no_pesanan,
                        'id_user' => $val->id_user,
                        'id_produk' => $val->id_produk,
                        'qty' => $val->qty,
                        'total_harga' => $val->total_harga,
                        'tanggal_pemesanan' => $val->tanggal_pemesanan,
                        'status_pembayaran' => 'Y'
                    ]);

                    $updatestokmaster = Product::where('id', $val->id_produk);

                    $updatestokmaster->update([
                        'qty_stok' => $sisa_stok
                    ]);

                    Cache::flush();

                    return response()->json([
                        'success' => true,
                        'message' => 'Pembayaran Berhasil',
                        'data' => $var,
                    ]);
                }
            }
        }else{
            return response()->json([
                'success' => true,
                'message' => 'Tidak ada pesanan denga no tersebut',
                'data' => $var,
            ]);
        }


        $validator = Validator::make($request->all(), [
            'no_pesanan'  => 'required',
            'id_produk' => 'required',
            'qty' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'success' => false,
                'message' => 'Kolom Wajib Diisi!',
                'data' => $validator->errors()
            ],401);
        } else {

            $getharga=Product::select('harga_produk')->where('id', $request->input('id_produk'))->first();

            $harga_produk=$getharga->harga_produk;

            $total_harga=$request->input('qty')*$harga_produk;

            $array[] = array(

                'no_pesanan' => $request->input('no_pesanan'),
                'id_user' => $request->input('id_user'),
                'id_produk' => $request->input('id_produk'),
                'qty' => $request->input('qty'),
                'total_harga' => $total_harga,
                'tanggal_pembayaran' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            );

            $out = array_values($array);

            Redis::set('addchartproduk'.$request->input('no_pesanan'), json_encode($out)
            );

            if ($out) {
                return response()->json([
                    'success' => true,
                    'message' => 'List Produk Berhasil Ditambah!',
                    'data' => $out
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'List Produk Gagal Ditambah!',
                ], 400);
            }
        }
    }
}
