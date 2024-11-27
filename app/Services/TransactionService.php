<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class TransactionService extends Service
{
    public function __construct(Transaction $transaction)
    {
        parent::__construct($transaction, 'transaction');
    }

    public function getAllTransactions(int $perPage = self::DEFAULT_PER_PAGE): LengthAwarePaginator
    {
        return $this->remember('transactions.all', fn () => $this->paginate($this->model->query(), $perPage));
    }

    public function getTransactionById(int $id): Transaction
    {
        return $this->remember(
            $this->getCacheKey('transaction', $id),
            fn () => $this->model->findOrFail($id)
        );
    }

    public function createTransaction(array $data): Transaction
    {
        $transaction = $this->model->create($data);
        $this->clearModelCache($transaction->id, ['transaction']);
        return $transaction;
    }

    public function updateTransaction(int $id, array $data): Transaction
    {
        $transaction = $this->getTransactionById($id);
        $transaction->update($data);
        
        $this->clearModelCache($id, ['transaction']);
        
        return $transaction->fresh();
    }

    public function deleteTransaction(int $id): bool
    {
        $transaction = $this->getTransactionById($id);
        $this->clearModelCache($id, ['transaction']);
        return $transaction->delete();
    }

    public function getTransactionsByStatus(string $status): Collection
    {
        return $this->remember("transactions.{$status}", function () use ($status) {
            return $this->model->where('status', $status)
                ->orderBy('due_date', 'asc')
                ->get();
        });
    }
}
