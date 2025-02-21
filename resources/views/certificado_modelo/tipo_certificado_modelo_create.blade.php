<!--View apresentada em gestor -->

@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="/css/acoes/create.css">
    <link rel="stylesheet" href="/css/modelo_certificado/modelo_certificado.css">
    <link rel="stylesheet" href="/css/cadastros/cadastrarAcao.css">
    <link rel="stylesheet" href="/css/modelo_certificado/modal-legendas.css">
@endsection

@section('content')
    <div class="row">

        <div class="container container-form-modelo">
            <h2 class="text-center">Criar modelo de certificado</h2>

            <!--icone p ativar o modal -->


            <p class="d-flex align-items-center justify-content-center">

                <span class="all-text">
                    Clique
                    <a class="link-modal" data-bs-toggle="modal" data-bs-target="#modal-Legenda">aqui</a>
                    para visualizar as variáveis
                </span>

                <a data-bs-toggle="modal" data-bs-target="#modal-Legenda">
                    <img class="lamp-legendas-icon" src="/images/modal-legenda/lamp.svg" alt="variaveis">
                </a>

            </p>

            <form action="{{ Route('tipo_certificado_modelo.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-box-modelo-certificado form-row">

                    <input name="fundo" type="hidden" id="imagem" value="{{ $modelo->fundo }}">
                    <input name="verso" type="hidden" id="verso" value="{{ $modelo->verso }}">
                    <input name="unidade_administrativa_id" type="hidden" id="unidade_administrativa_id"
                        value=" {{ $modelo->unidade_administrativa_id }} ">

                    <div class="row box col-xl-7">
                        <div class="campo input-create-box d-flex aligm-items-start justify-content-start flex-column">
                            <span class="tittle-input">Descrição</span>
                            <input class="w-75 input-text " type="text" name="descricao" required>
                        </div>
                    </div>

                    <div class="row box col-xl-7">
                        <div class="campo input-create-box d-flex aligm-items-start justify-content-start flex-column">
                            <span class="tittle-input">Unidade Administrativa</span>
                            <input class="w-75 input-text " type="text" value="{{ $unidade_adm->descricao }}" disabled>
                        </div>
                    </div>

                    <div class="row box col-xl-7">
                        <div
                            class="campo spacing-row2 input-create-box d-flex align-items-start justify-content-start flex-column">
                            <span class="tittle-input">Tipo Certificado</span>
                            <select class="select-form w-100 " name="tipo_certificado" id="select_tipo_certificado"
                                required>
                                @foreach ($tipos_certificado as $tipo)
                                    <option value="{{ $tipo }}">{{ $tipo }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row box col-xl-7" id="outro_tipo_certificado" style="display: none;">
                        <div class="campo input-create-box d-flex aligm-items-start justify-content-start flex-column">
                            <span class="tittle-input">Tipo Certificado (outro)</span>
                            <input class="w-75 input-text" type="text" name="outro">
                        </div>
                    </div>

                    <div class="row box d-flex flex-column col-xl-7">

                        <span class="tittle-input w-100">Texto padrão:</span>


                        <textarea name="texto" class="w-100 campo input-create-box text-area-campo" id="texto">
                        {{ $modelo->texto }}
                    </textarea>
                    </div>

                    <div class="row d-flex align-items-center justify-content-evenly col-xl-7">

                        <div class="col-5">
                            <span>Fundo: </span>
                            <div class="card-preview">
                                <img src="{{ $img_fundo }}" alt="">

                            </div>
                        </div>
                        <div class="col-5">
                            <span>Verso: </span>
                            <div class="card-preview">

                                <img src="{{ $img_verso }}" alt="">
                            </div>
                        </div>

                    </div>


                    <div class="row d-flex justify-content-start align-items-center mt-4 col-xl-7">

                        <div class="col d-flex justify-content-evenly align-items-center input-create-box border-0">
                            <a class="button mt-4 d-flex justify-content-center align-items-center cancel"
                                href="{{ route('certificado_modelo.index', ['id' => $modelo->id]) }}">Voltar</a>

                            <button class="button mt-4 submit" type="submit">Salvar</button>

                        </div>
                    </div>

                </div>
            </form>
        </div>

        
        <!--modal legendas -->
        @include('components.modal-Legenda')

    </div>


    <script src="/js/modelo_certificado/modelo_certificado-edit.js"></script>
    <script src="/js/modelo_certificado/modelo_certificado-geral.js" ></script>
@endsection
