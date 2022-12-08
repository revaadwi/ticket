<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Ticket;
use Illuminate\Support\Facades\Validator;


class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $transactions = Transaction::get();

        return response()->json([
            'success' => true,
            'message' => 'List Semua Transaksi',
            'data' => $transactions
        ], 200);
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
            'id_ticket' => 'required|integer',
            'quantity' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validated = $validator->validated();

        $ticket = Ticket::find($request->id_ticket);
        $user = auth()->user();

        if ($ticket->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tiket tidak mencukupi',
                'data' => ''
            ], 400);
        }
        
        $total = $ticket->concert_price * $request->quantity;

        $transaction = Transaction::create([
            'id_ticket' => $request->id_ticket,
            'id_user' => $user->id,
            'quantity' => $request->quantity,
            'total_price' => $total,
            'booking_date' => now(),
        ]);

        if ($transaction) {
            $ticket->stock = $ticket->stock - $request->quantity;
            $ticket->save();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil ditambahkan',
                'data' => $transaction
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi gagal ditambahkan',
                'data' => ''
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $transaction = Transaction::find($id);

        if ($transaction) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Transaksi',
                'data' => $transaction
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
                'data' => ''
            ], 404);
        }
    }

    /**
     * Update the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transaction $transaction)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $validated = $validator->validated();

        $transaction->status = $request->status;
        $transaction->save();

        if ($transaction) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diupdate',
                'data' => $transaction
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi gagal diupdate',
                'data' => ''
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        if ($transaction) {
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus',
                'data' => $transaction
            ], 200);
        }
    }
}
