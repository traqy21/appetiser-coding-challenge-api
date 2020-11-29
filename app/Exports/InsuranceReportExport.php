<?php


namespace App\Exports;


use App\Helpers\Helper;
use App\Repositories\InsuranceRepository;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InsuranceReportExport implements FromCollection, WithHeadings
{
    protected $startDate, $endDate;
    protected $insuranceRepository;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->insuranceRepository = app()->make(InsuranceRepository::class);
    }

    public function collection() {
        $list = $this->insuranceRepository->listByDateRange(
            $this->startDate,
            $this->endDate
        );

        if($list->count() == 0){
            return collect([]);
        }

        $data = [];

        foreach ($list->get() as $item){

            $data[] = [
                "customer" => $item->customer->firstname . " " . $item->customer->lastname,
                "address" => $item->customer->address,
                "policy_code" => $item->reference,
                "transaction_type" => ($item->renew_count > 0) ? "Renewal": "New Business",
                "registration_date" => Helper::formatDate($item->registration_date, 'm/d/Y'),
                "vehicle_release_date" => Helper::formatDate($item->vehicle->release_date, 'm/d/Y'),
                "vehicle_description" => $item->vehicle->description,
                "vehicle_variant" => $item->vehicle->variant,
                "vehicle_plate_no" => $item->vehicle->plate_no . "|" . $item->vehicle->conduction_sticker,
                "vehicle_mode_of_payment" => $item->vehicle->mode_of_payment,
                "vehicle_length_of_payment" => $item->vehicle->length_of_payment,
                "financing_company" => $item->vehicle->financing_company,
                "insurance_type" => ($item->is_in_house > 0) ? "In-House": "Outsourced",
                "insurer" => ($item->owned > 0) ? "Toyota Insure" : "Non-Toyota Insure",
                "insurance_company" => $item->provider,
                "policy_amount" => $item->total_amount_insured,
            ];
        }

//        Log::Debug('data', $data);

        return collect($data);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // TODO: Implement headings() method.
        return [
                "Customer",
                "Address",
                "Policy Code",
                "Transaction Type",
                "Date of policy",
                "Date of Vehicle Release",
                "Vehicle Model Description",
                "Vehicle Model Variant",
                "Plate Number/Conduction Sticker",
                "Vehicle Mode of Payment",
                "Vehicle Length of Payment",
                "Financing Company",
                "Type Of Insurance",
                "Insurer",
                "Insurance Company ",
                "Policy Amount",
        ];
    }
}