<?php

namespace MyCore\Http\Response;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Helpers\General;

trait ResponseFormatTrait
{
    private $responseData = [
        'status' => General::CODE_SUCCESS,
        'description' => 'Chưa thiết lập response.',
        'data' => []
    ];

    /**
     * Set response data
     *
     * @param int $errorCode
     * @param string $message
     * @param array $data
     */
    protected function responseJson($errorCode, $message = null, $data = null, $status = 200)
    {
        $json = new JsonResponse();
        $json->setData($data);
        $data = $json->getData(1);

        if (isset($data['first_page_url'])) {
            unset($data['first_page_url']);
            unset($data['last_page_url']);
            unset($data['links']);
            unset($data['next_page_url']);
            unset($data['prev_page_url']);
            unset($data['path']);
        }

        $this->responseData['data']        = $data;
        $this->responseData['status']      = $errorCode;
        $this->responseData['description'] = $message ?: ($errorCode == General::CODE_SUCCESS
            ? __('search.processed_successfully') : __('search.processing_failed'));

        if ($errorCode == General::CODE_ERROR) {
            Log::notice('user-' . auth()->id() . ' - ' . $this->responseData['description'] . ' - ' . url()->full(), [
                'ip' => app('request')->ip(),
                'params' => app('request')->all(),
                'errors' => $data
            ]);
        }

        return response()->json($this->responseData, $status);
    }

    protected function responseSuccess($message = null, $data = null, $status = 200)
    {
        if (isset($data['first_page_url'])) {
            unset($data['first_page_url']);
            unset($data['last_page_url']);
            unset($data['links']);
            unset($data['next_page_url']);
            unset($data['prev_page_url']);
            unset($data['path']);
        }

        $this->responseData['data']        = $data;
        $this->responseData['status']      = General::CODE_SUCCESS;
        $this->responseData['description'] = $message ?: __('search.processed_successfully');

        return response()->json($this->responseData, $status);
    }

    protected function responseError($message = null, $data = null, $status = 200, $log = true)
    {
        $this->responseData['data']        = $data;
        $this->responseData['status']      = General::CODE_ERROR;
        $this->responseData['description'] = $message ?: __('search.processing_failed');

        if ($log) {
            Log::notice('user-' . auth()->id() . ' - ' . $this->responseData['description'] . ' - ' . url()->full(), [
                'ip' => app('request')->ip(),
                'params' => app('request')->all(),
                'errors' => $data
            ]);
        }

        return response()->json($this->responseData, $status);
    }

    protected function responseDownload($file, $filename = null, $deleteFileAfterSend = false)
    {
        $info = pathinfo($file);

        if (!$filename) {
            $filename = $info['filename'] . "." . $info['extension'];
        } elseif (!pathinfo($filename, PATHINFO_EXTENSION)) {
            $filename = trim($filename, ".") . "." . $info['extension'];
        }
        $filename = urlencode($filename);

        return response()->download($file, $filename)->deleteFileAfterSend($deleteFileAfterSend);
    }
}
