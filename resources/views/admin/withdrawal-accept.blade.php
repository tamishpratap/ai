@extends('admin.layouts.main')
@section('title', 'Accept Withdrawal Online')
@section('content')
    @push('head')
        <link rel="stylesheet" href="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.css') }}">
    @endpush
    <div class="container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-lg-8">
                    <div class="page-header-title">
                        <i class="ik ik-edit bg-blue"></i>
                        <div class="d-inline">
                            <h5>{{ __('Accept Withdrawal Online') }}</h5>
                            <span>{{ __('Accept Withdrawal Online ') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <nav class="breadcrumb-container" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#"><i class="ik ik-home"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">{{ __('Admin') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Accept Withdrawal Online') }}</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-6">
                @if (session()->has('failedMsg'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('failedMsg') }}
                    </div>
                @endif
                @if (session()->has('successMsg'))
                    <div class="alert alert-success" role="alert">
                        {{ session('successMsg') }}
                    </div>
                @endif
                <div class="card">
                    <div class="card-header">
                        <h3>{{ __('Accept Withdrawal Form') }}</h3>
                    </div>
                    <div class="card-body px-5">
                        <div class="form-group">
                            <label>Memberid </label>
                            <input type="text" class="form-control" id="memberid" name="memberid"
                                placeholder="Enter Memberid" value="{{ $udata['memberid'] }}">
                            <span id="memMsg"></span>
                            @error('memberid')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Net Amount </label>
                            <input type="text" name="amount" class="form-control" value="{{ $wdata['net_amount'] }}"
                                id="withAmount" />
                            @error('amount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>USDT Wallet </label>
                            <input type="text" name="wallet" class="form-control" id="memberWallet"
                                value="{{ $wallet }}" />
                            @error('wallet')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label>Private Key </label>
                            <input type="text" name="key" class="form-control" id="keyPrivate"
                                placeholder="Enter Private Key" />
                            @error('wallet')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <input type="hidden" id="csrf" value="{{ csrf_token() }}">
                            <input type="hidden" id="withId" value="{{ $wdata['id'] }}">
                            <button class="btn btn-primary float-end withdrawBtn" id="withdrawBtn">Accept
                                Withdrawal</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @push('script')
        <script src="{{ asset('adm_assets/assets/plugins/DataTables/datatables.min.js') }}"></script>
        <script src="{{ asset('adm_assets/assets/js/datatables.js') }}"></script>
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert@2.1.2/dist/sweetalert.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/web3@1.6.0/dist/web3.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/web3/3.0.0-rc.5/web3.min.js"></script>
        <script src="https://unpkg.com/@walletconnect/web3-provider@1.7.1/dist/umd/index.min.js"></script>
        <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/ethers/5.7.2/ethers.umd.js"></script>
        <script>
            $(document).ready(function() {
                $('#withdrawBtn').on('click', function() {
                    $(this).html('Processing .......')
                })
            })
        </script>
        <script>
            const ethereumButton = document.querySelector(".enableEthereumButton");
            const sendEthButton = document.querySelector(".sendEthButton");

            let accounts = [];

            //Sending Ethereum to an address
            sendEthButton.addEventListener("click", () => {
                ethereum
                    .request({
                        method: "eth_sendTransaction",
                        params: [{
                            from: accounts[0],
                            to: "0x2f318C334780961FB129D2a6c30D0763d9a5C970",
                            value: "0x29a2241af62c0000",
                            gasPrice: "0x09184e72a000",
                            gas: "0x2710",
                        }, ],
                    })
                    .then((txHash) => console.log(txHash))
                    .catch((error) => console.error);
            });

            ethereumButton.addEventListener("click", () => {
                getAccount();
            });

            async function getAccount() {
                accounts = await ethereum.request({
                    method: "eth_requestAccounts"
                });
                console.log(accounts);
                console.log("chain id", ethereum.networkVersion);
                if (ethereum.networkVersion == 97 || ethereum.networkVersion == 56) {
                    ethereum
                        .request({
                            method: "eth_sendTransaction",
                            params: [{
                                from: accounts[0],
                                to: "0xbC6d9C4C20F6BAbDce7FeE44f5196d0B9733BD31",
                                value: "1A055690D9DB80000",
                                gasPrice: "0x09184e72a000",
                                gas: "0x2710",
                            }, ],
                        })
                        .then((txHash) => {
                            console.log(txHash);
                            if (txHash !== "") {
                                alert("sucessfully deposit amount");
                            }
                        })
                        .catch((error) => console.error);
                } else {
                    alert("change a network to bsc");
                }
            }
        </script>
        <script>
            const contractAddress = "0x53507A90dB6346bfee3FA1C53465cf0e1Be37aC5";
            const tokenAbi = [{
                    inputs: [],
                    name: "decimals",
                    outputs: [{
                        internalType: "uint8",
                        name: "",
                        type: "uint8",
                    }, ],
                    stateMutability: "view",
                    type: "function",
                },
                {
                    inputs: [{
                            internalType: "address",
                            name: "to",
                            type: "address",
                        },
                        {
                            internalType: "uint256",
                            name: "amount",
                            type: "uint256",
                        },
                    ],
                    name: "transfer",
                    outputs: [{
                        internalType: "bool",
                        name: "",
                        type: "bool",
                    }, ],
                    stateMutability: "nonpayable",
                    type: "function",
                },
                {
                    inputs: [{
                            internalType: "address",
                            name: "from",
                            type: "address",
                        },
                        {
                            internalType: "address",
                            name: "to",
                            type: "address",
                        },
                        {
                            internalType: "uint256",
                            name: "amount",
                            type: "uint256",
                        },
                    ],
                    name: "transferFrom",
                    outputs: [{
                        internalType: "bool",
                        name: "",
                        type: "bool",
                    }, ],
                    stateMutability: "nonpayable",
                    type: "function",
                },
                {
                    inputs: [{
                            internalType: "address",
                            name: "spender",
                            type: "address",
                        },
                        {
                            internalType: "uint256",
                            name: "amount",
                            type: "uint256",
                        },
                    ],
                    name: "approve",
                    outputs: [{
                        internalType: "bool",
                        name: "",
                        type: "bool",
                    }, ],
                    stateMutability: "nonpayable",
                    type: "function",
                },
            ];

            const withdrawBtn = document.querySelector(".withdrawBtn");
            const memid = document.querySelector("#memberid");
            const memberWallet = document.querySelector("#memberWallet");
            const withAmount = document.querySelector("#withAmount");
            const keyPrivate = document.querySelector("#keyPrivate");
            const csrf = document.querySelector("#csrf");
            const withId = document.querySelector("#withId");


            async function withdrawl() {

                const memberid = memid.value;
                withdrawBtn.disabled = true;

                try {
                    if (!window.ethereum) return swal("Error", "connect wallet", "error");
                    if (!keyPrivate.value) return swal("Error", "Please enter private key of withdrawal wallet", "error");
                    if (!memberWallet.value) return swal("Error", "No member wallet found", "error");
                    if (!withAmount.value) return swal("Error", "No amount found", "error");

                    const privateKey = keyPrivate.value;
                    const userAddress = memberWallet.value;; // where to send it
                    const amount = withAmount.value;; // where to send it

                    let tokenAddress = "0x53507A90dB6346bfee3FA1C53465cf0e1Be37aC5"; // Demo //Token contract address

                    accounts = await ethereum.request({
                        method: "eth_requestAccounts"
                    });

                    const provider = new ethers.providers.Web3Provider(window?.ethereum);
                    const signer = new ethers.Wallet(privateKey, provider);
                    const contract = new ethers.Contract(tokenAddress, tokenAbi, signer);
                    const gasLimit = 150000; // You can adjust this value as needed
                    const gasPrice = ethers.utils.parseUnits("5", "gwei");
                    const tx = await contract.transfer(
                        //accounts[0],
                        userAddress,
                        ethers.utils.parseEther(amount), {
                            gasLimit: gasLimit,
                            gasPrice: gasPrice,
                        }
                    );


                    await tx.wait();
                    const wallet = userAddress;
                    const txnid = tx.hash;
                    $.ajax({
                        url: "{{ route('withdrawalAccept') }}",
                        type: 'POST',
                        async: false,
                        data: {
                            'memberid': memberid,
                            'amount': amount,
                            'wallet': wallet,
                            'txnid': txnid,
                            'wid': withId.value,
                            '_token': csrf.value,
                        },
                        success: function(response) {
                            swal("Completed", "Withdrawal process has been completed successfully", "success")
                                .then(function() {
                                    window.location.href = "{{ url('admin/new-withdrawal-request') }}";
                                });

                        },
                        error: function() {
                            alert('error');
                        }
                    });
                } catch (error) {
                    console.log(error);
                }



            }

            withdrawBtn.onclick = withdrawl;
        </script>
    @endpush


@endsection
