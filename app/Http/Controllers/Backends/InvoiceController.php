<?php

namespace App\Http\Controllers\Backends;

use Exception;
use App\Models\User;
use GuzzleHttp\Client;
use App\Models\Payment;
use App\Helpers\PdfGenerator;
use App\Notifications\InvoicePaid;
use Barryvdh\DomPDF\Facade as PDF;
use App\Http\Controllers\Controller;
use App\Models\UserContract;
use GuzzleHttp\Exception\RequestException;

class InvoiceController extends Controller
{
    public function sendInvoiceToTelegram($userId)
{
    try {
        $user = User::findOrFail($userId);
        $contract = UserContract::where('user_id', $user->id)->firstOrFail();
        $invoiceData = Payment::with('paymentamenities', 'userContract', 'paymentutilities')
            ->where('user_contract_id', $contract->id)
            ->latest()
            ->firstOrFail();
            
        // Generate PDF
        $pdfPath = PdfGenerator::generatePdf(
            'backends.invoice._invoice', 
            ['invoiceData' => $invoiceData, 'user' => $user], 
            "invoice_{$user->id}"
        );
        
        // Check if PDF exists and if user has a Telegram ID
        if (!file_exists($pdfPath)) {
            return redirect()->route('payments.index')
                ->with('error', 'Failed to generate PDF file.');
        }
        
        if (empty($user->telegram_id)) {
            return redirect()->route('payments.index')
                ->with('error', 'User does not have a Telegram ID.');
        }
            
        // Send notification with invoice data
        $user->notify(new InvoicePaid($invoiceData));
        
        // Send PDF directly to telegram
        $this->sendTelegramPdf($user->telegram_id, $pdfPath);
        
        return redirect()->route('payments.index')
            ->with('success', 'Invoice sent successfully to Telegram.');
            
    } catch (\Exception $e) {
        \Log::error('Failed to send invoice to Telegram: ' . $e->getMessage());
        return redirect()->route('payments.index')
            ->with('error', 'Failed to send invoice: ' . $e->getMessage());
    }
}

// Add this method to your controller to send the PDF file
protected function sendTelegramPdf($telegramId, $pdfPath)
{
    if (empty($telegramId)) {
        throw new \Exception('Telegram ID is required.');
    }
    
    if (!file_exists($pdfPath)) {
        throw new \Exception('PDF file not found at: ' . $pdfPath);
    }
    
    $telegramBotToken = '6892001713:AAEFqGqO4bqaQmNx465sQxV-Z6Cq-HHQCsw';
    if (empty($telegramBotToken)) {
        throw new \Exception('Telegram Bot Token is not configured.');
    }
    
    $client = new \GuzzleHttp\Client();
    
    // Send document to Telegram
    $response = $client->post("https://api.telegram.org/bot{$telegramBotToken}/sendDocument", [
        'multipart' => [
            [
                'name' => 'chat_id',
                'contents' => $telegramId
            ],
            [
                'name' => 'document',
                'contents' => fopen($pdfPath, 'r'),
                'filename' => basename($pdfPath)
            ],
            [
                'name' => 'caption',
                'contents' => 'Your invoice is attached.'
            ]
        ]
    ]);
    
    $result = json_decode($response->getBody()->getContents(), true);
    
    if (!isset($result['ok']) || $result['ok'] !== true) {
        throw new \Exception('Failed to send document to Telegram: ' . json_encode($result));
    }
    
    return true;
}
    public function downloadInvoice($userId)
    {
        try {
            $user = User::where('id', $userId)->first();
            if (!$user) {
                return 'User not found';
            }
            $contract = UserContract::where('user_id', $user->id)->first();
            $invoiceData = Payment::with('paymentamenities', 'userContract', 'paymentutilities')->where('user_contract_id', $contract->id)->latest()->first();

            $pdfPath = PdfGenerator::generatePdf('backends.invoice._invoice', ['invoiceData' => $invoiceData, 'user' => $user], "invoice_{$user->id}");

            if (file_exists($pdfPath)) {
                return response()->download($pdfPath, "invoice_{$user->name}.pdf")->deleteFileAfterSend(true);
            } else {
                return response()->json(['message' => 'PDF file not found.'], 404);
            }
        } catch (Exception $e) {
            dd($e);
            return redirect()->route('payments.index')->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
    protected function sendTelegramInvoice($telegramUserId, $filePath)
    {
        $botToken = '6892001713:AAEFqGqO4bqaQmNx465sQxV-Z6Cq-HHQCsw';
        $chatId = $telegramUserId;
        $url = "https://api.telegram.org/bot{$botToken}/sendDocument";

        $client = new Client();

        try {
            $file = fopen($filePath, 'r');

            $response = $client->post($url, [
                'multipart' => [
                    [
                        'name' => 'chat_id',
                        'contents' => $chatId,
                    ],
                    [
                        'name' => 'document',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => basename($filePath),
                    ],
                    [
                        'name' => 'caption',
                        'contents' => 'Here is your invoice.',
                    ],
                ],
            ]);
            fclose($file);

            return json_decode($response->getBody());

        } catch (RequestException $e) {
            return response()->json(['message' => 'Error sending invoice: ' . $e->getMessage()], 500);
        }
    }
    public function viewInvoiceDetails($userId)
    {
        $user = User::findOrFail($userId);
        $contract = UserContract::where('user_id', $user->id)->first();
        $invoiceData = Payment::with('paymentamenities', 'userContract', 'paymentutilities')->where('user_contract_id', $contract->id)->latest()->first();

        return view('backends.payment.partial.payment_details', compact('user', 'invoiceData'));
    }

    public function printInvoice($userId)
    {
        $user = User::findOrFail($userId);
        $contract = UserContract::where('user_id', $user->id)->first();
        $invoiceData = Payment::with('paymentamenities', 'userContract', 'paymentutilities')
            ->where('user_contract_id', $contract->id)
            ->latest()
            ->first();

        return view('backends.invoice._invoice_slim', compact('user', 'invoiceData'));
    }
    public function downloadUtilitiesInvoice($userId)
    {
        try {
            $user        = User::findOrFail($userId);
            $contract    = UserContract::where('user_id', $user->id)->first();
            $invoiceData = Payment::with('paymentamenities', 'userContract', 'paymentutilities')->where('user_contract_id', $contract->id)->latest()->first();

            $pdfPath = PdfGenerator::generatePdf('backends.invoice._utilities_invoice', ['invoiceData' => $invoiceData, 'user' => $user], "utilities_invoice_{$user->id}");

            if (file_exists($pdfPath)) {
                return response()->download($pdfPath, "utilities_invoice_{$user->name}.pdf")->deleteFileAfterSend(true);
            } else {
                return response()->json(['message' => 'PDF file not found.'], 404);
            }
        } catch (Exception $e) {
            return redirect()->route('payments.index')->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }

}
