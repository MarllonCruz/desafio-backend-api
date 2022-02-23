<?php

namespace App\Models;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\TransactionDeniedException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'transactions';

    /** @var string */
    protected $primaryKey = 'id';

    /** @var string */
    protected $keyType = 'string';

    /** @var bool */
    public $incrementing = false;
    
    /**
      * @var array
      */
    protected $fillable = ['id', 'title', 'value', 'type'];

    /**
     * @var array
     */
    protected $hidden = ['created_at', "updated_at"];

    /**
     * @param array $data
     * @return Transaction
     */
    public function registre(array $data): ?Transaction
    {
        if (!$data['title'] || !$data['type'] || !$data['value']) {
            throw new TransactionDeniedException('Preencha todos campos: title, type e value', 400);
        }

        if (!$data['value'] = filter_var($data['value'], FILTER_VALIDATE_FLOAT, 2)) {
            throw new TransactionDeniedException('Campo value tem que ser número inteiro ou decimal', 400);
        }

        if (!in_array($data['type'], ['income', 'outcome'])) {
            throw new TransactionDeniedException('Campo type tem que ser income ou outcome', 400);
        }

        $this->id    = Uuid::uuid4()->toString();
        $this->title = $data['title'];
        $this->value = floatval(sprintf('%0.2f', $data['value']));
        $this->type  = $data['type'];

        if ($this->type == 'outcome') {
            if ($this->value > $this->balance()->total) {
                throw new TransactionDeniedException('Saldo insuficiente para sacar', 400);
            }
        } 

        $this->save();
        return $this::find($this->id);
    }

    /**
     * @return object
     */
    public static function balance(): object
    {   
        $balance = new \stdClass();
        $balance->income  = 0;
        $balance->outcome = 0;
        $balance->total   = 0;

        $balance->income  = floatval(Transaction::where('type', 'income')->sum('value'));
        $balance->outcome = floatval(Transaction::where('type', 'outcome')->sum('value'));
        $balance->total   = floatval(sprintf('%0.2f', ($balance->income - $balance->outcome)));

        return $balance;
    }

    /**
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function getTotalTransactions(): Collection
    {
        $transactions = Transaction::orderBy('created_at', 'DESC')->get();

        foreach ($transactions as $transaction) {
            $transaction['value'] = floatval(sprintf('%0.2f', ($transaction['value'])));
        }

        return $transactions;
    }
}
