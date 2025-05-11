<x-layouts.app :title="__('Dashboard')">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pines UI</title>
    <style>[x-cloak]{display:none}</style>
</head>
<body class="flex items-start justify-center h-full bg-gray-50">
    <div class="flex items-center justify-center w-full max-w-full">
        <div 
    x-data="{
        text: '',
        textArray : ['Alpine JS is Amazing', 'It is Truly Awesome!', 'You Have to Try It!'],
        textIndex: 0,
        charIndex: 0,
        typeSpeed: 110,
        cursorSpeed: 550,
        pauseEnd: 1500,
        pauseStart: 20,
        direction: 'forward',
    }" 
    x-init="$nextTick(() => {
        let typingInterval = setInterval(startTyping, $data.typeSpeed);
    
        function startTyping(){
            // Make this function local to this component only
            if (typeof window.startTyping === 'undefined') {
                window.startTyping = function() {}; // Create an empty function to avoid errors
            }
            
            let current = $data.textArray[ $data.textIndex ];
            
            // check to see if we hit the end of the string
            if($data.charIndex > current.length){
                    $data.direction = 'backward';
                    clearInterval(typingInterval);
                    
                    setTimeout(function(){
                        typingInterval = setInterval(startTyping, $data.typeSpeed);
                    }, $data.pauseEnd);
            }   
                
            $data.text = current.substring(0, $data.charIndex);
            
            if($data.direction == 'forward')
            {
                $data.charIndex += 1;
            } 
            else 
            {
                if($data.charIndex == 0)
                {
                    $data.direction = 'forward';
                    clearInterval(typingInterval);
                    setTimeout(function(){
                        $data.textIndex += 1;
                        if($data.textIndex >= $data.textArray.length)
                        {
                            $data.textIndex = 0;
                        }
                        typingInterval = setInterval(startTyping, $data.typeSpeed);
                    }, $data.pauseStart);
                }
                $data.charIndex -= 1;
            }
        }
                    
        setInterval(function(){
            // Check if cursor element exists before accessing its classList
            const cursorElement = $refs.cursor;
            if (cursorElement && cursorElement.classList) {
                if(cursorElement.classList.contains('hidden'))
                {
                    cursorElement.classList.remove('hidden');
                } 
                else 
                {
                    cursorElement.classList.add('hidden');
                }
            }
        }, $data.cursorSpeed);

    })"
    class="flex items-center justify-center mx-auto text-center max-w-7xl">
    <div class="relative flex items-center justify-center h-auto">
        <p class="text-2xl font-black leading-tight" x-text="text"></p>
        <span class="absolute right-0 w-2 -mr-2 bg-black h-3/4" x-ref="cursor"></span>
    </div>
</div>
    </div>
</body>

</x-layouts.app>
