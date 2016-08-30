function showReturnPolicy(){
	new Effect.Appear('return-policy', { duration: 0.2, from: 0, to: 1 });
	new Effect.Appear('return-policy-overlay', { duration: 0.2, from: 0, to: 1 });
	$('return-policy-overlay').observe('click', function(){
		hideReturnPolicy();
	});
}

function hideReturnPolicy(){
	new Effect.Fade('return-policy', { duration: 0.2 });
	new Effect.Fade('return-policy-overlay', { duration: 0.2 });
}