<template>
    <div class="token-widget-container">
        ARCD: {{ arcdBalance }}<br />
        ETH: {{ ethBalance }}<br />
        Network: {{ network }}
    </div>
</template>

<style>
    .token-widget-container {
        position: fixed;
        text-align: right;
        color: white;
        width: 200px;
        top: 25px;
        right: 15px;
        z-index: 10;
        font-family: monospace;
        font-size: 16px;
    }

    /* .inventory-bar-image {
        image-rendering: pixelated;
        width: 800px;
        max-width: 90%;
    } */
</style>

<script>

export default {

    data: function() {
        return {
            account: null,
            network: 'none',
            arcdBalance: '?',
            ethBalance: '?'
        }
    },

    async mounted() {
        if (typeof window.ethereum !== 'undefined') {
            console.log('window.ethereum:', window.ethereum)

            switch (window.ethereum.networkVersion) {
                case '1':
                    this.network = 'mainnet'
                    break
                case '2':
                    this.network = 'morden (DEPRECATED)'
                    break
                case '3':
                    this.network = 'ropsten'
                    break
                default:
                    this.network = 'unknown'
                    break
            }

            const accounts = await ethereum.enable()
            const account = accounts[0]
            console.log(account)
            // this.addARCDtoMetamask()
            this.account = account
            this.queryVendorContract()
            this.checkEthBalance()
            this.checkArcdBalance()
            this.getETHprice()
            // this.clickBuyArcd(0.2)
        } else {
            console.log('Not using MetaMask')
        }
    },

    methods: {
        // Change token exchange rate
        changeExchangeRate(rate = 40000) {
            var vendorAddr = '0x9592A1D9118710C41A0E19280F852f6e321Fb1c7'; // mainnet
            var vendorABI = [
                {
            		"constant": false,
            		"inputs": [
            			{
            				"name": "newTokenExchangeRate",
            				"type": "uint256"
            			}
            		],
            		"name": "setExchangeRate",
            		"outputs": [],
            		"payable": false,
            		"stateMutability": "nonpayable",
            		"type": "function"
            	},
                {"constant":true,"inputs":[],"name":"minBuyTokens","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"tokenExchangeRate","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"buyPrice","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"ARCD_TOKEN_ADDRESS","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"payable":true,"stateMutability":"payable","type":"fallback"},{"anonymous":false,"inputs":[{"indexed":true,"name":"_sender","type":"address"},{"indexed":true,"name":"_value","type":"uint256"},{"indexed":true,"name":"_rate","type":"uint256"},{"indexed":false,"name":"_tokens","type":"uint256"}],"name":"Purchase","type":"event"}
            ]
            var vendorInst = web3.eth.contract(vendorABI).at(vendorAddr);

            vendorInst.setExchangeRate(rate, function(err, result) {
                if (result) {
                    console.log(result)
                } else {
                    console.log(err)
                }
            })
        },

        // Get the current ETH price in USD
        getETHprice() {
            let url = 'https://min-api.cryptocompare.com/data/price?fsym=ETH&tsyms=USD&api_key=f3a8bdf14c66fc65780176d4d7b8d92e5b423fdeb9861f747c9bf469751e7374'
            axios.get(url)
                 .then(res => {
                     console.log(res)
                     console.log(res.data.USD)
                     const ethPriceUsd = res.data.USD
                 })
        },

        // Generate buy transaction
        clickBuyArcd(ethToSend = 0.1, network = "mainnet") {
            // let ethToSend = this.state.ethToSend
            // let network = this.state.network

            switch (network) {
                case "ropsten":
                    var vendorAddr = '0xd1BEDd362b60d2Ed38C0820b3f97E64D47795DeA'; // ropsten
                    break;
                case "mainnet":
                    var vendorAddr = '0x9592A1D9118710C41A0E19280F852f6e321Fb1c7'; // mainnet
                    break;
                default:
                    var vendorAddr = null
                    break;
            }

            var sendTransactionCallback = (function(err, result) {
                if (result) {
                    console.log('ok')
                    console.log(result)
                    // this.setState({
                    //     ethSent: true,
                    //     txId: result
                    // })
                } else {
                    console.log(err.message)
                }
            }).bind(this)

            web3.eth.sendTransaction({
                from: web3.eth.accounts[0],
                to: vendorAddr,
                value: ethToSend * 1000000000000000000,
                gas: 200000
            }, sendTransactionCallback)

        },

        // Get user's ETH balance
        checkEthBalance () {
            web3.eth.getBalance(web3.eth.accounts[0], (err, balance) => {
                const ethBalance = balance.c[0] / 10000
                this.ethBalance = ethBalance
                console.log('ETH balance:', ethBalance)
            })
        },

        // Get user's ARCD balance
        checkArcdBalance (network = "mainnet") {
          var addr = web3.eth.accounts[0]
          // console.log('Checking ARCD balance - network is ' + network)

          switch(network) {
            case "ropsten":
              var tokenAddress = '0x7ba509375e2fae3a0860a2a0b82bd975cb30e6b0'; // ropsten
              break;
            case "mainnet":
              var tokenAddress = '0xb581e3a7db80fbaa821ab39342e9cbfd2ce33c23'; // mainnet
              break;
            default:
              var tokenAddress = null
              break;
          }

          var tokenABI = [{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[{"name":"who","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"}]
          var tokenInst = web3.eth.contract(tokenABI).at(tokenAddress);
          var myArcdBalanceCallback = (function(err, result) {
            if (result) {
                const arcdBalance = result.c[0] / 10000
                this.arcdBalance = arcdBalance
                console.log("ARCD balance: ", arcdBalance)
            } else {
                console.log("err: ", err)
            }
          }).bind(this)

          tokenInst.balanceOf.call(addr, myArcdBalanceCallback)
      },

        // Get exchange rate and minimum token purchase at 0.01 ETH
        queryVendorContract() {
            var vendorAddr = '0x9592A1D9118710C41A0E19280F852f6e321Fb1c7'; // mainnet
            var vendorABI = [
                {"constant":true,"inputs":[],"name":"minBuyTokens","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"tokenExchangeRate","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"buyPrice","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"ARCD_TOKEN_ADDRESS","outputs":[{"name":"","type":"address"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[],"payable":false,"stateMutability":"nonpayable","type":"constructor"},{"payable":true,"stateMutability":"payable","type":"fallback"},{"anonymous":false,"inputs":[{"indexed":true,"name":"_sender","type":"address"},{"indexed":true,"name":"_value","type":"uint256"},{"indexed":true,"name":"_rate","type":"uint256"},{"indexed":false,"name":"_tokens","type":"uint256"}],"name":"Purchase","type":"event"}
            ]
            var vendorInst = web3.eth.contract(vendorABI).at(vendorAddr);

            vendorInst.tokenExchangeRate.call(function(err, result) {
                console.log('tokenExchangeRate:', result.c[0])
                // this.setState({ tokenExchangeRate: result.c[0] })
            }.bind(this))

            vendorInst.minBuyTokens.call(function(err, result) {
                console.log('minBuyTokens:', result.c[0] / 10000)
                // this.setState({ minBuyTokens: result.c[0] / 10000 })
            }.bind(this))
        },

        // Prompt user to show their ARCD balance in their Metamask
        addARCDtoMetamask() {
            const tokenAddress = '0xb581e3a7db80fbaa821ab39342e9cbfd2ce33c23'
            const tokenSymbol = 'ARCD'
            const decimals = 18
            const tokenImage = 'https://arcade.city/images/token-sq1.png'

            ethereum.sendAsync({
                method: 'wallet_watchAsset',
                params: {
                  "type":"ERC20",
                  "options":{
                    "address": tokenAddress,
                    "symbol": tokenSymbol,
                    "decimals": decimals,
                    "image": tokenImage,
                  },
                },
                id: Math.round(Math.random() * 100000),
            }, (err, added) => {
                if (added) {
                    console.log('Thanks for your interest!')
                } else {
                    console.log('Your loss!')
                }
            })
        }
    }


}
</script>
