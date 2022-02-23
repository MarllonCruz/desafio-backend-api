<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Exceptions\TransactionDeniedException;

/**
 *  @group Wallet Management
 * 
 * APIs to manage the user resource
 */
class WalletController extends Controller
{   
    /**
     * Make a transaction in the wallet
     * 
     * @queryParam title string required Title for transaction
     * @queryParam value int required Value for transaction
     * @queryParam type string required Type field must be "income" or "outcome"
     * 
     * @response 200 {
     *  "id": "uuid",
     *  "title": "Pagamento da fatura",
     *  "value": 500,
     *  "type": "income"
     * }
     * 
     * @param Request $request
     */
    public function setTransaction(Request $request)
    {   
        if (!$request->has('title', 'type', 'value')) {
            return response()->json(['error' => 'Parâmetros: title, type e value são obrigatório'], 400);
        }

        $data = filter_var_array($request->only('title', 'value', 'type'), FILTER_SANITIZE_STRIPPED);
        $transction = new Transaction();
        
        try {
            $transction->registre($data);
            return response()->json($transction, 200);
            
        } catch (TransactionDeniedException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }

        return response()->json($transction, 200);
    }

    /**
     * Get all transactions and total amount in balance
     * 
     * @response 200 {
     *    "transactions": [
     *      {
     *       "id": "uuid",
     *       "title": "Salário",
     *      "value": 4000,
     *       "type": "income"
     *       },
     *       {
     *       "id": "uuid",
     *       "title": "Freela",
     *       "value": 2000,
     *       "type": "income"
     *       },
     *       {
     *       "id": "uuid",
     *       "title": "Pagamento da fatura",
     *       "value": 4000,
     *       "type": "outcome"
     *       },
     *       {
     *       "id": "uuid",
     *       "title": "Cadeira Gamer",
     *       "value": 1200,
     *       "type": "outcome"
     *       }
     *   ],
     *   "balance": {
     *       "income": 6000,
     *       "outcome": 5200,
     *       "total": 800
     *       }
     *   }
     * 
     * @return array
     */
    public function getTransactions()
    {   
        return response()->json([
            'transactions' => Transaction::getTotalTransactions(),
            'balance'      => Transaction::balance()
        ], 200);
    }
}
