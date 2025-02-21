<?php

namespace App\Http\Controllers;

use App\Models\Acao;
use App\Models\Atividade;
use App\Models\Instituicao;
use App\Models\Participante;
use App\Models\User;
use App\Models\Natureza;
use Illuminate\Http\Request;
use App\Http\Requests\StoreParticipanteRequest;
use App\Http\Requests\UpdateParticipanteRequest;
use App\Validates\ParticipanteValidator;
use App\Validates\DefaultValidator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Utils\Mask;
use Illuminate\Support\Facades\Mail;
use App\Mail\UsuarioNaoCadastrado;
use App\Rules\Cpf;


class ParticipanteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($atividade_id)
    {
        $participantes = Participante::all()->where('atividade_id', $atividade_id)->sortBy('id');
        $atividade = Atividade::findOrFail($atividade_id);
        $acao = Acao::findOrFail($atividade->acao_id);

        $cont = 0;

        return view('participante.participante_index',compact('participantes','atividade','acao','cont'));
    
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($atividade_id, Request $request)
    {
        $cpf = $request->all()['cpf'];

        $atividade = Atividade::findOrFail($atividade_id);
        $user = User::where('cpf', $cpf)
            ->first();
        $instituicaos = Instituicao::all();

        if ($user)
            return view('participante.participante_create', ['atividade' => $atividade, 'user' => $user, 'instituicaos' => $instituicaos]);

        return view('participante.participante_create', ['atividade' => $atividade, 'cpf' => $cpf, 'instituicaos' => $instituicaos]);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreParticipanteRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $attributes = $request->all();

        
        if(!$attributes['instituicao']){
            $instituicao = Instituicao::find($attributes['instituicao_id']);

            $attributes['instituicao'] = $instituicao ? $instituicao->nome : "Outras"; 
        }
            
        try {
            ParticipanteValidator::validate($attributes);
        } catch (ValidationException $exception) {
            return redirect(route('participante.create', ['atividade_id' => $attributes['atividade_id'], 'cpf' => $attributes['cpf']]))
                ->withErrors($exception->validator)->withInput();
        }

        $atividade = Atividade::find($attributes['atividade_id']);

        if($atividade->participantes->where('user.cpf', $attributes['cpf'])->first()){
            return redirect(Route('participante.index', ['atividade_id' => $attributes['atividade_id']]))
                            ->with(['error_mensage' => 'Não é possível adicionar o mesmo participante mais de uma vez na mesma atividade!']);
        }

        try{
            $user = $this->createUser($attributes);
        
        } catch (ValidationException $exception) {
            return redirect()->back()->withErrors($exception->validator)->withInput();
        }

        $attributes['user_id'] = $user->id;
        Participante::create($attributes);

        return redirect(Route('participante.index', ['atividade_id' => $attributes['atividade_id']]))
            ->with(['mensagem' => 'Participante cadastrado com sucesso']);
    }

    private function createUser($attributes)
    {
        $user = User::where('cpf', $attributes['cpf'])
            ->first();

        if ($user)
            return $user;

        $password = Str::random(15);
        $userAttributes = [
            'name' => $attributes['nome'],
            'email' => $attributes['email'],
            'cpf' => $attributes['cpf'],
            'instituicao_id' => $attributes['instituicao_id'] ?? 2,
            'instituicao' => $attributes['instituicao'] ?? "Outras",
            'password' => Hash::make($password),
            'perfil_id' => 4
        ];

        $userAttributes['password_confirmation'] = $userAttributes['password'];

        DefaultValidator::validate($userAttributes, User::$rules, User::$messages);
        DefaultValidator::validate($userAttributes, ['cpf' => ['required', 'unique:users', new Cpf]], User::$messages);

        //enviar o email informando a senha 
        Mail::to($userAttributes['email'], $userAttributes['name'])->send(new UsuarioNaoCadastrado([
            'email'    => $userAttributes['email'],
            'password' => $password,
        ]));


        return User::create($userAttributes);

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Participante $participante
     * @return \Illuminate\Http\Response
     */
    public function show()
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Participante $participante
     * @return \Illuminate\Http\Response
     */
    public function edit($participante_id)
    {
        $participante = Participante::findOrFail($participante_id);

        $atividade = Atividade::findOrFail($participante->atividade_id);

        return view('participante.participante_edit', ['participante' => $participante, 'atividade' => $atividade]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateParticipanteRequest $request
     * @param \App\Models\Participante $participante
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            ParticipanteValidator::validate($request->all(), Participante::$editRules);
        } catch (ValidationException $exception) {
            return redirect(route('participante.edit', ['participante_id' => $request->id]))
                ->withErrors($exception->validator)->withInput();
        }

        $participante = Participante::findOrFail($request->id);

        $participante->titulo = $request->titulo;
        $participante->carga_horaria = $request->carga_horaria;
        $participante->atividade_id = $request->atividade_id;

        $participante->update();

        return redirect(Route('participante.index', ['atividade_id' => $request->atividade_id]))
            ->with(['mensagem' => 'Participante editado com sucesso']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Participante $participante
     * @return \Illuminate\Http\Response
     */
    public function delete($participante_id)
    {
        $participante = Participante::findOrFail($participante_id);
        $participante->delete();

        return redirect(Route('participante.index', ['atividade_id' => $participante->atividade_id]))->with(['mensagem' => 'Participante excluido com sucesso']);
    }

    public function participantes_atividade($atividade_id)
    {
        $participantes = Participante::all()->where('atividade_id', $atividade_id)->sortBy('id');
        $atividade = Atividade::find($atividade_id);

        return view('gestor_institucional.participantes_acao', ['participantes' => $participantes, 'atividade' => $atividade]);
    }

    public function participante_certificados()
    {
        $naturezas = Natureza::all();
        
        return view('participante.certificados', compact('naturezas'));
    }

    public function filtro(){
        $participacoes = Auth::user()->participacoes;


        if(request('buscar_acao')){

            $participacoes = Participante::search_acao($participacoes, request('buscar_acao'));
        }


        if(request('data')){
            $participacoes = Participante::search_data($participacoes, request('data'));
        }

        if(request('natureza')){
            $participacoes = Participante::search_natureza($participacoes, request('natureza'));
        }

        return view('participante.list_certificados',compact('participacoes'));
    }


    public function import_participantes($atividade_id, Request $request){
        $atividade = Atividade::find($atividade_id);        
        
        $file = fopen($request->participantes_csv, "r");

        fgetcsv($file); //ler o cabeçalho

        $participantes = [];

        while($row = fgetcsv($file)){
            //$row[0] => Nome | $row[1] = CPF | $row[2] = E-mail | $row[3] = CH
            $cpf = Mask::mask($row[1], "###.###.###-##");
            $user = User::where('cpf', '=', $cpf)->first();
        
            $confirm = True;

            if(!$user)
            {
                try{

                    $attributes = [
                        'nome' => $row[0],
                        'cpf'  => $cpf,
                        'email' => $row[2],
                        'perfil_id' => 4,
                        'instituicao' => 'outra',
                        'instituicao_id' => 2,
                    ];

                    $user = $this->createUser($attributes);

                } catch (\Throwable $th) {
                    $confirm = False;

                    $message = $row[0]." (".$th->getMessage().")";

                    array_push($participantes, $message);
                }
            } 


            if($confirm && !$user->participacoes()->where('atividade_id', '=', $atividade->id)->first())
            {
                $participante = new Participante();
                $participante->carga_horaria = $row[3];
                $participante->atividade_id = $atividade_id;
                $participante->user_id = $user->id;

                $participante->save();
            }

        }

        fclose($file);

        $mensagem = "Participantes adicionados!";

        if($participantes){
            $mensagem = "Os seguintes participantes não puderam ser adicionados:\n"
                        .implode("  /  ",$participantes).".\n".
                        "\n Verifique os dados dos participantes e tente novamente.";
            return redirect(route('participante.index', ['atividade_id' => $atividade_id]))->with(['alert_mensage' => $mensagem]);
        }

        return redirect(route('participante.index', ['atividade_id' => $atividade_id]))->with(['mensagem' => $mensagem]);
    }

}
