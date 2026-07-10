
// const ethereumButton = document.querySelector(".enableEthereumButton");
// const sendEthButton = document.querySelector(".sendEthButton");

// let accounts = [];

// //Sending Ethereum to an address
// sendEthButton.addEventListener("click", () => {
//     ethereum
//         .request({
//             method: "eth_sendTransaction",
//             params: [
//                 {
//                     from: accounts[0],
//                     to: "0x2f318C334780961FB129D2a6c30D0763d9a5C970",
//                     value: "0x29a2241af62c0000",
//                     gasPrice: "0x09184e72a000",
//                     gas: "0x2710",
//                 },
//             ],
//         })
//         .then((txHash) => console.log(txHash))
//         .catch((error) => console.error);
// });

// ethereumButton.addEventListener("click", () => {
//     getAccount();
// });

// async function getAccount() {
//     accounts = await ethereum.request({
//         method: "eth_requestAccounts",
//     });
//     console.log(accounts);
//     console.log("chain id", ethereum.networkVersion);
//     if (ethereum.networkVersion == 97 || ethereum.networkVersion == 56) {
//         ethereum
//             .request({
//                 method: "eth_sendTransaction",
//                 params: [
//                     {
//                         from: accounts[0],
//                         to: "0xbC6d9C4C20F6BAbDce7FeE44f5196d0B9733BD31",
//                         value: "1A055690D9DB80000",
//                         gasPrice: "0x09184e72a000",
//                         gas: "0x2710",
//                     },
//                 ],
//             })
//             .then((txHash) => {
//                 console.log(txHash);
//                 if (txHash !== "") {
//                     alert("sucessfully deposit amount");
//                 }
//             })
//             .catch((error) => console.error);
//     } else {
//         alert("change a network to bsc");
//     }
// }

const contractAddress = "0x55d398326f99059ff775485246999027b3197955";
const tokenAbi = [
    {
        inputs: [],
        name: "decimals",
        outputs: [
            {
                internalType: "uint8",
                name: "",
                type: "uint8",
            },
        ],
        stateMutability: "view",
        type: "function",
    },
    {
        inputs: [
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
        name: "transfer",
        outputs: [
            {
                internalType: "bool",
                name: "",
                type: "bool",
            },
        ],
        stateMutability: "nonpayable",
        type: "function",
    },
    {
        inputs: [
            {
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
        outputs: [
            {
                internalType: "bool",
                name: "",
                type: "bool",
            },
        ],
        stateMutability: "nonpayable",
        type: "function",
    },
    {
        inputs: [
            {
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
        outputs: [
            {
                internalType: "bool",
                name: "",
                type: "bool",
            },
        ],
        stateMutability: "nonpayable",
        type: "function",
    },
];


const transferButton = document.querySelector(".transferBtn");
const amount = document.querySelector("#amount");
const memberid = document.querySelector("#memberid");
const csrf = document.querySelector("#csrf");
const route = document.querySelector("#route").value;

async function depositActivation() {
    try {
        if (!window.ethereum)
            return swal("Not Connected", "Please connect wallet", "error");
        if (!amount.value)
            return swal("No Amount", "Please enter amount to import", "error");
        if (amount.value < 0)
            return swal("Invalid", "Invalid input entered", "error");
        if (amount.value < 1)
            return swal("Sorry", "Minimum deposit amount is $10", "error");
        if (amount.value > 10000)
            return swal("Sorry", "Maximun deposit amount is $10000", "error");
        document.querySelector(".transferBtn").innerHTML =
            "Wait! Processing...";


        const accounts = await ethereum.request({ method: "eth_requestAccounts" });
        const chainId = await ethereum.request({ method: "eth_chainId" });

        // Ensure connected to BSC mainnet
        if (chainId != 56) { // 0x38 is the chain ID for Binance Smart Chain Mainnet
            swal("Wrong Network", "Please connect to Binance Smart Chain Mainnet", "error");
            return;
        }

        const provider = new ethers.providers.Web3Provider(window.ethereum);
        const signer = provider.getSigner(accounts[0]);
        const tokenAddress = "0x55d398326f99059ff775485246999027b3197955"; // USDT BEP20 address
        const contract = new ethers.Contract(tokenAddress, tokenAbi, signer);

        const tx = await contract.transfer(
            "0xe29e3685b47aabe2ade8b79c95fe5727b24b8d92", // Receiver address
            ethers.utils.parseUnits(amount.value, 18) // Convert to smallest unit (18 decimals for USDT)
        );

        await tx.wait();
        const txnid = tx.hash;
        //Ajax code for activation
        $.ajax({
            url: route,
            type: "POST",
            data: {
                memberid: memberid.value,
                amount: amount.value,
                txnid: txnid,
                _token: csrf.value,
            },
            success: function (response) {
                //alert(response['value']);
                swal("Congratulations!", "Amount has been successfully imported", "success").then(function () {
                    window.location.reload();
                });
            },
            error: function () {
                alert("error");
            },
        });
    } catch (error) {
        console.log(error);
        swal(
            "Checkout",
            "Please check your wallet account balance",
            "error"
        ).then(function () {
            window.location.reload();
        });
    }
}
transferButton.addEventListener("click", depositActivation);

// async function upgradeAccount() {
//     const csrf = document.querySelector("#csrf");
//     const memberWallet = document.querySelector("#memberWallet");
//     const amount = document.getElementById("upgrade_deposit");

//     try {
//         if (!window.ethereum) return swal("Error", "connect wallet", "error");
//         if (amount.value < 0)
//             return swal("Invalid", "Invalid input entered", "error");

//         accounts = await ethereum.request({
//             method: "eth_requestAccounts",
//         });
//         if (ethereum.networkVersion != 56)
//             return swal(
//                 "Not Connected",
//                 "Please connect to BNB Smart Chain Network",
//                 "error"
//             );
//         const provider = new ethers.providers.Web3Provider(window?.ethereum);
//         const signer = provider.getSigner(accounts[0]);
//         let tokenAddress = "0x55d398326f99059ff775485246999027b3197955";
//         const contract = new ethers.Contract(tokenAddress, tokenAbi, signer);

//         const tx = await contract.transfer(
//             "0x237d39932AfCaE3C583FF39080606533357e3DB5",
//             ethers.utils.parseEther(amount.value)
//         );
//         await tx.wait();
//         const txnid = tx.hash;
//         //Ajax code for activation
//         $.ajax({
//             url: "{{ route('upgradeUser') }}",
//             type: "POST",
//             data: {
//                 memberWallet: memberWallet.value,
//                 amount: amount.value,
//                 txnid: txnid,
//                 _token: csrf.value,
//             },
//             success: function (response) {
//                 if (response["code"] == 1) {
//                     swal(
//                         "Upgraded",
//                         "Account has been upgraded successfully",
//                         "success"
//                     ).then(function () {
//                         window.location.href = "{{ url('member/dashboard') }}";
//                     });
//                 } else {
//                     swal(
//                         "Error",
//                         "Package amount is not correct",
//                         "error"
//                     ).then(function () {
//                         window.location.href = "{{ url('member/dashboard') }}";
//                     });
//                 }
//             },
//             error: function () {
//                 alert("error");
//             },
//         });
//     } catch (error) {
//         console.log(error);
//         swal(
//             "Checkout",
//             "Please check your wallet account balance",
//             "error"
//         ).then(function () {
//             window.location.href = "{{ url('member/dashboard') }}";
//         });
//     }
// }

// async function withdrawl() {
//     const userAddress = memberWallet.value;
//     withdrawBtn.disabled = true;

//     try {
//         if (!window.ethereum) return swal("Error", "connect wallet", "error");
//         var balance;
//         $.ajax({
//             url: "{{ route('balValidate') }}",
//             type: "POST",
//             async: false,
//             data: {
//                 member_wallet: userAddress,
//                 _token: csrf.value,
//             },
//             success: function (response) {
//                 balanaceResult = response["data"];
//                 //alert(response['code']);
//             },
//             error: function () {
//                 // alert('error');
//             },
//         });
//         const balanceRslt = String(balanaceResult);
//         if (balanceRslt == 0)
//             return swal(
//                 "No Balance",
//                 "There is no balance in account",
//                 "error"
//             ).then(function () {
//                 window.location.reload();
//             });

//         var keyPrivate;

//         $.ajax({
//             url: "{{ route('getPrivateKey') }}",
//             type: "POST",
//             async: false,
//             data: {
//                 member_wallet: userAddress,
//                 _token: csrf.value,
//             },
//             success: function (response) {
//                 keyPrivate = response["data"];
//             },
//             error: function () {
//                 alert("error");
//             },
//         });
//         const privateKey = keyPrivate;

//         let tokenAddress = "0x55d398326f99059ff775485246999027b3197955"; // Demo //Token contract address
//         let toAddress = userAddress; // where to send it
//         accounts = await ethereum.request({
//             method: "eth_requestAccounts",
//         });

//         const provider = new ethers.providers.Web3Provider(window?.ethereum);
//         const signer = new ethers.Wallet(privateKey, provider);
//         const contract = new ethers.Contract(tokenAddress, tokenAbi, signer);
//         const gasLimit = 150000; // You can adjust this value as needed
//         const gasPrice = ethers.utils.parseUnits("5", "gwei");
//         const tx = await contract.transfer(
//             accounts[0],
//             ethers.utils.parseEther(balanceRslt),
//             {
//                 gasLimit: gasLimit,
//                 gasPrice: gasPrice,
//             }
//         );
//         console.log(tx);
//         await tx.wait();
//         const txnid = tx.hash;
//         $.ajax({
//             url: "{{ route('initiate-withdrawal') }}",
//             type: "POST",
//             async: false,
//             data: {
//                 memberWallet: userAddress,
//                 amount: balanceRslt,
//                 txnid: txnid,
//                 _token: csrf.value,
//             },
//             success: function (response) {
//                 swal(
//                     "Completed",
//                     "Withdrawal process has been completed successfully",
//                     "success"
//                 ).then(function () {
//                     window.location.href = "{{ url('member/dashboard') }}";
//                 });
//             },
//             error: function () {
//                 alert("error");
//             },
//         });
//     } catch (error) {
//         console.log(error);
//     }
// }

// withdrawBtn.onclick = withdrawl;

// $(document).ready(function () {
//     var status = $("#status").val();
//     if (status == "Temp") {
//         $("#activation").modal("show");
//     } else {
//         $("#activation").modal("hide");
//     }

//     // activation button
//     $(".activate_deposit").on("click", function () {
//         $(this).html(
//             'Processing <img src="{{asset("uploads/images/march-balls.gif")}}">'
//         );
//     });

//     // upgrade button
//     $(".upgradeBtn").on("click", function () {
//         $(this).html(
//             'Processing <img src="{{asset("uploads/images/march-balls.gif")}}">'
//         );
//     });

//     // withdrawal button
//     $(".withdraw_btn").on("click", function () {
//         $(this).html(
//             'Processing <img src="{{asset("uploads/images/march-balls.gif")}}">'
//         );
//     });

//     //referal link
//     $("#copyButtonId").on("click", function () {
//         var text = $("#code").text();
//         var $temp = $("<input>");
//         $("body").append($temp);
//         $temp.val(text).select();
//         document.execCommand("copy");
//         $temp.remove();
//         $("#msgText").html("Text Copied");
//     });
// });
