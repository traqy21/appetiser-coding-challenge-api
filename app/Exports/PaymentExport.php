<?php


namespace App\Exports;

use App\Repositories\PaymentRepository;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentExport implements FromCollection, WithHeadings
{
    protected $startDate, $endDate;
    protected $paymentRepository;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->paymentRepository = app()->make(PaymentRepository::class);
    }

    public function collection() {
        $list = $this->paymentRepository->listByDateRange(
            $this->startDate,
            $this->endDate
        );

        if($list->count() == 0){
            return collect([]);
        }

        $data = [];

        foreach ($list->get() as $item){
            $customer = $item->insurance->customer;

            $data[] = [
                "date" => $item->payment_date,
                "reference" => $item->reference,
                "customer" => $customer->firstname . " " . $customer->lastname,
                "amount" =>  $item->amount
            ];
        }

        Log::Debug('data', $data);

        return collect($data);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // TODO: Implement headings() method.
        return [
            "Date",
            "Reference #",
            "Customer",
            "Amount"
        ];
    }
}