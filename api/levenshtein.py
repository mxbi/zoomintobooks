import difflib

def match(match_str, word_list):
	result = []
	titles = [t[0] for t in word_list]
	closest_matches = difflib.get_close_matches(match_str, titles, cutoff=0.1)
	for res in closest_matches:
		i = titles.index(res)
		if i != -1:
			result.append(word_list[i][1])
			
	return result
