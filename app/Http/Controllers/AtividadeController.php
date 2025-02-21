<?php

namespace App\Http\Controllers;

use App\Models\Acao;
use App\Models\Atividade;
use App\Models\Participante;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAtividadeRequest;
use App\Http\Requests\UpdateAtividadeRequest;
use App\Validates\AtividadeValidator;
use Illuminate\Validation\ValidationException;

class AtividadeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($acao_id)
    {
        $acao = Acao::find($acao_id);
        
        $acao->get_participantes_name();
        
        $atividades = $acao->atividades->sortBy('id');

        return view('atividade.atividade_index', ['atividades' => $atividades, 'acao' => $acao]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($acao_id)
    {
        $acao = Acao::findOrFail($acao_id);
        $descricoes = ['Avaliador(a)', 'Bolsista', 'Colaborador(a)', 'Comissão Organizadora', 'Conferencista', 'Coordenador(a)', 'Formador(a)', 'Ministrante', 'Orientador(a)',
                        'Palestrante', 'Voluntário(a)', 'Participante', 'Vice-coordenador(a)', 'Ouvinte', 'Outra'];


        return view('atividade.atividade_create', ['acao' => $acao, 'descricoes' => $descricoes]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreAtividadeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            AtividadeValidator::validate($request->all());
        } catch (ValidationException $exception) {
            return redirect(route('atividade.create', ['acao_id' => $request->acao_id]))->withErrors($exception->validator)->withInput();;
        }

        if($request->descricao == 'Outra')
        {
            $atividade = new Atividade();

            $atividade->descricao = $request->outra;
            $atividade->data_inicio = $request->data_inicio;
            $atividade->data_fim = $request->data_fim;
            $atividade->acao_id = $request->acao_id;

            $atividade->save();

        } else
        {
            Atividade::create($request->all());
        }


        return redirect(route('atividade.index', ['acao_id' => $request->acao_id]))->with(['mensagem' => "Atividade cadastrada com sucesso."]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Atividade  $atividade
     * @return \Illuminate\Http\Response
     */
    public function show(Atividade $atividade)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Atividade  $atividade
     * @return \Illuminate\Http\Response
     */
    public function edit($atividade_id)
    {
        $atividade = Atividade::findOrFail($atividade_id);

        $acao = Acao::findOrFail($atividade->acao_id);

        $descricoes = ['Avaliador(a)', 'Bolsista', 'Colaborador(a)', 'Comissão Organizadora', 'Conferencista', 'Coordenador(a)', 'Formador(a)', 'Ministrante', 'Orientador(a)',
            'Palestrante', 'Voluntário(a)', 'Particiante', 'Vice-coordenador(a)', 'Ouvinte'];

        return view('atividade.atividade_edit', ['atividade' => $atividade, 'acao' => $acao, 'descricoes' => $descricoes]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateAtividadeRequest  $request
     * @param  \App\Models\Atividade  $atividade
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            AtividadeValidator::validate($request->all());
        } catch (ValidationException $exception) {
            return redirect(route('atividade.edit', ['atividade_id' => $request->id]))
                            ->withErrors($exception->validator)->withInput();
        }


        $atividade = Atividade::findOrFail($request->id);

        //$atividade->status = $request->status;
        $atividade->descricao = $request->descricao;
        $atividade->data_inicio = $request->data_inicio;
        $atividade->data_fim = $request->data_fim;
        $atividade->acao_id = $request->acao_id;

        $atividade->update();

        return redirect(Route('atividade.index', ['acao_id' => $request->acao_id]))
                                ->with(['mensagem'=>'Atividade editada com sucesso']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Atividade  $atividade
     * @return \Illuminate\Http\Response
     */
    public function delete($atividade_id)
    {
        $atividade = Atividade::findOrFail($atividade_id);

        $atividade->participantes()->delete();

        $atividade->delete();


        return redirect(Route('atividade.index', ['acao_id' => $atividade->acao_id]))
                                ->with(['mensagem' => 'Atividade excluida com sucesso']);
    }
}
