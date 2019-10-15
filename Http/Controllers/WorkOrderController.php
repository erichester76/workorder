<?php

namespace Modules\WorkOrder\Http\Controllers;

use Auth;
use App\Http\Controllers\BaseController;
use App\Services\DatatableService;
use Modules\WorkOrder\Datatables\WorkOrderDatatable;
use Modules\WorkOrder\Repositories\WorkOrderRepository;
use Modules\WorkOrder\Http\Requests\WorkOrderRequest;
use Modules\WorkOrder\Http\Requests\CreateWorkOrderRequest;
use Modules\WorkOrder\Http\Requests\UpdateWorkOrderRequest;

class WorkOrderController extends BaseController
{
    protected $WorkOrderRepo;
    //protected $entityType = 'workorder';

    public function __construct(WorkOrderRepository $workorderRepo)
    {
        //parent::__construct();

        $this->workorderRepo = $workorderRepo;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('list_wrapper', [
            'entityType' => 'workorder',
            'datatable' => new WorkOrderDatatable(),
            'title' => mtrans('workorder', 'workorder_list'),
        ]);
    }

    public function datatable(DatatableService $datatableService)
    {
        $search = request()->input('sSearch');
        $userId = Auth::user()->filterId();

        $datatable = new WorkOrderDatatable();
        $query = $this->workorderRepo->find($search, $userId);

        return $datatableService->createDatatable($datatable, $query);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(WorkOrderRequest $request)
    {
        $data = [
            'workorder' => null,
            'method' => 'POST',
            'url' => 'workorder',
            'title' => mtrans('workorder', 'new_workorder'),
        ];

        return view('workorder::edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(CreateWorkOrderRequest $request)
    {
        $workorder = $this->workorderRepo->save($request->input());

        return redirect()->to($workorder->present()->editUrl)
            ->with('message', mtrans('workorder', 'created_workorder'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(WorkOrderRequest $request)
    {
        $workorder = $request->entity();

        $data = [
            'workorder' => $workorder,
            'method' => 'PUT',
            'url' => 'workorder/' . $workorder->public_id,
            'title' => mtrans('workorder', 'edit_workorder'),
        ];

        return view('workorder::edit', $data);
    }

    /**
     * Show the form for editing a resource.
     * @return Response
     */
    public function show(WorkOrderRequest $request)
    {
        return redirect()->to("workorder/{$request->workorder}/edit");
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(UpdateWorkOrderRequest $request)
    {
        $workorder = $this->workorderRepo->save($request->input(), $request->entity());

        return redirect()->to($workorder->present()->editUrl)
            ->with('message', mtrans('workorder', 'updated_workorder'));
    }

    /**
     * Update multiple resources
     */
    public function bulk()
    {
        $action = request()->input('action');
        $ids = request()->input('public_id') ?: request()->input('ids');
        $count = $this->workorderRepo->bulk($ids, $action);

        return redirect()->to('workorder')
            ->with('message', mtrans('workorder', $action . '_workorder_complete'));
    }
}