<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Expense;
use App\Budget;
use Auth;

class ExpensesController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @param \Illuminate\Http\Request
     * @param string $name
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, string $name)
    {
        $date = $request->session()->get('date');
        $budgets = Budget::where('user_id', Auth::id())->get();
        $selectedBudget = Budget::where('user_id', Auth::id())->where('name', $name)->first();
        $dateTime = Carbon::createFromTimestamp($date)->toDateTimeString();
        return view('expenses.create')
            ->with('budgets', $budgets)
            ->with('selectedBudget', $selectedBudget)
            ->with('navBudgets', $budgets->take(5))
            ->with('date', $date)
            ->with('dateTime', $dateTime);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function store(Request $request)
    {
        $this->validate($request,
            [
                'budget_id' => 'required',
                'place' => 'required',
                'date' => 'required|date',
                'price' => 'required|between:0,99.99',
                'reason' => 'max:255'
            ]);

        $expense = Expense::create([
            'budget_id' => $request->budget_id,
            'place' => $request->place,
            'date' => $request->date,
            'price' => $request->price,
            'reason' => $request->reason
        ]);

        return redirect()->route('expense.show', ['name' => $expense->budget->name]);
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request
     * @param  string $name
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, string $name)
    {
        $date = $request->session()->get('date');
        $firstDayOfMonth = date('Y-m-01', $date);
        $lastDayOfMonth = date('Y-m-t', $date);
        $budget = Budget::where('user_id', Auth::id())->where('name', $name)->first();
        $selectedBudget = Budget::where('user_id', Auth::id())->where('name', $name)->first();
        $userBudgets = Budget::where('user_id', Auth::id())->get();

        if ($budget) {
            $expenses = Expense::where('budget_id', $budget->id)
                ->where('date', '>=', $firstDayOfMonth)
                ->where('date', '<=', $lastDayOfMonth)
                ->orderBy('date', 'asc')
                ->get();
            $spent = $expenses->sum('price');
            $remaining = $budget->amount - $spent;
        } else {
            $expenses = collect(new Expense);
            $spent = 0;
            $remaining = 0;
        }

        return view('expenses.index')
            ->with('expenses', $expenses)
            ->with('navBudgets', $userBudgets->take(5))
            ->with('date', $date)
            ->with('budget', $budget)
            ->with('selectedBudget', $selectedBudget)
            ->with('spent', $spent)
            ->with('remaining', $remaining);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \Illuminate\Http\Request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $date = $request->session()->get('date');
        $expense = Expense::find($id);
        $budgets = Budget::where('user_id', Auth::id())->get();

        return view('expenses.edit')
            ->with('expense', $expense)
            ->with('budgets', $budgets)
            ->with('navBudgets', $budgets->take(5))
            ->with('budget', Budget::find($id))
            ->with('date', $date);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {
        $this->validate($request,
            [
                'budget_id' => 'required',
                'place' => 'required',
                'date' => 'required|date',
                'price' => 'required|between:0,99.99',
                'reason' => 'max:255'
            ]);

        $expense = Expense::find($id);
        $expense->budget_id = $request->budget_id;
        $expense->place = $request->place;
        $expense->date = $request->date;
        $expense->price = $request->price;
        $expense->reason = $request->reason;
        $expense->save();

        return redirect()->route('expense.show', ['name' => $expense->budget->name]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $expense = Expense::find($id);
        $expense->delete();

        return redirect()->back();
    }

    /**
     * Go to previous month
     *
     * @param  \Illuminate\Http\Request $request
     * @param string $name
     * @return \Illuminate\Http\Response
     */
    public function prev(Request $request, string $name)
    {
        $date = $request->session()->pull('date');
        $prevMonth = date('Y-m-01', strtotime('-1 month', $date));
        $request->session()->put('date', strtotime($prevMonth));

        return redirect()->route('expense.show', ['name' => $name]);
    }

    /**
     * Go to next month
     *
     * @param  \Illuminate\Http\Request $request
     * @param string $name
     * @return \Illuminate\Http\Response
     */
    public function next(Request $request, string $name)
    {
        $date = $request->session()->pull('date');
        $prevMonth = date('Y-m-01', strtotime('+1 month', $date));
        $request->session()->put('date', strtotime($prevMonth));

        return redirect()->route('expense.show', ['name' => $name]);
    }
}
